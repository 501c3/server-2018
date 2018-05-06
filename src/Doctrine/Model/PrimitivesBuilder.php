<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/2/18
 * Time: 5:16 PM
 */

namespace App\Doctrine\Model;
use App\Doctrine\Builder;
use App\Entity\Configuration\Miscellaneous;
use App\Entity\Models\Domain;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Exceptions\ModelExceptionCode;
use App\Exceptions\GeneralException;
use App\Repository\Configuration\MiscellaneousRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\TagRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\Status;
use App\Utils\YamlPosition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;

/**
 * Class PrimitivesBuilder
 * @package App\Doctrine\Model
 */
class PrimitivesBuilder extends Builder
{
    /**
     * @var DomainRepository
     */
    private $domainRepository;
    /**
     * @var ValueRepository
     */
    private $valueRepository;
    /**
     * @var TagRepository
     */
    private $tagRepository;
    /**
     * @var MiscellaneousRepository
     */
    private $miscellaneousRepository;

    private $domainHash=[];

    private $valueHash=[];

    private $positionHash=[];

    private $tagHash = [];


    /**
     * PrimitivesBuilder constructor.
     * @param DomainRepository $domainRepository
     * @param ValueRepository $valueRepository
     * @param TagRepository $tagRepository
     * @param MiscellaneousRepository $miscellaneousRepository
     * @param TraceableEventDispatcher|null $eventDispatcher
     */
    public function __construct(
        DomainRepository $domainRepository,
        ValueRepository  $valueRepository,
        TagRepository $tagRepository,
        MiscellaneousRepository $miscellaneousRepository,
        TraceableEventDispatcher $eventDispatcher=null)
    {
        $this->domainRepository = $domainRepository;
        $this->valueRepository = $valueRepository;
        $this->tagRepository = $tagRepository;
        $this->miscellaneousRepository = $miscellaneousRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $yamlTxt
     * @param bool $rebuild
     * @return bool
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function build(string $yamlTxt, bool $rebuild=false):bool
    {
        $result = YamlPosition::parse( $yamlTxt );
        $this->sendStatus(Status::COMMENCE,YamlPosition::getLineCount());
        $data=current($result['data']);
        $position=current($result['position']);
        while ($data && $position){
            $this->buildPrimitivesSection($data, $position);
            $data=next($result['data']);
            $position=next($result['position']);
        }
        $this->domainRepository->getEntityManager()->flush();
        $this->sendStatus(Status::COMPLETE,100);
        if($rebuild){
            return true;
        }
        $emMisc=$this->miscellaneousRepository->getEntityManager();
        $miscellaneous=$this->miscellaneousRepository->findOneBy(['name'=>'primitives']);
        if ($miscellaneous) {
            $miscellaneous->setText($yamlTxt);
        } else {
            $miscellaneous=new Miscellaneous();
            $miscellaneous->setName('primitives')
                            ->setText($yamlTxt);
            $emMisc->persist($miscellaneous);
        }
        $emMisc->flush($miscellaneous);
        return true;
    }




    /**
     * @param string $filename
     * @return bool
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function buildFromFile(string $filename):bool
    {
        $contents=file_get_contents($filename);
        return $this->build($contents);
    }


    /**
     * @return bool
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function rebuild()
    {
        $miscellaneous=$this->miscellaneousRepository->findOneBy(['name'=>'primitives']);
        /** @var Miscellaneous $miscellaneous*/
        $yamlText=$miscellaneous->getText();
        return $this->build($yamlText);
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    private function buildPrimitivesSection($data, $position)
    {

        list($dataPart, $positionPart, $dataKey, $dataKeyPosition) = $this->current($data, $position);
        while($dataPart && $positionPart){
            $keys=['comment','domain','event-tag'];
            if(!in_array($dataKey, $keys)){
                throw new GeneralException($dataKey,  $dataKeyPosition,
                    "expected \"".join('","',$keys)."\".",
                    ModelExceptionCode::KEY);
            }
            $this->buildSubsection($dataPart, $positionPart, $dataKey);
            list($dataPart, $positionPart, $dataKey, $dataKeyPosition) = $this->next($data, $position);
        }

    }

    /**
     * @param $data
     * @param $position
     * @param $key
     * @throws GeneralException
     */
    private function buildSubsection($data, $position, $key)
    {
        switch($key){
            case 'comment':
                break;
            case 'domain':
                $this->buildDomains($data, $position);
                break;
            case 'event-tag':
                $this->buildEventTag($data, $position);
                break;
        }
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    public function buildDomains($data, $position)
    {
        $keys = ['style', 'substyle', 'proficiency', 'age', 'type', 'tag', 'dance'];
        list( $dataPart, $positionPart, $domainKey, $positionKey ) = $this->current( $data, $position );
        while ($dataPart && $positionPart) {
            if (in_array( $domainKey, $keys )) {
                if (!isset( $this->domainHash[$domainKey] )) {
                    $this->createDomain( $domainKey );
                }
                $this->buildDomainValues( $dataPart, $positionPart, $domainKey );
            } else {
                throw new GeneralException(
                    $domainKey,$positionKey,
                    ".  Expected \"". join( '","', $keys).".\"",
                    ModelExceptionCode::PRIMITIVE_DOMAINS);
            }
            list( $dataPart, $positionPart, $domainKey, $positionKey ) = $this->next( $data, $position );
        }
    }

    /**
     * @param $dataRecords
     * @param $positionRecords
     * @param $domainKey
     * @throws GeneralException
     */
    private function buildDomainValues($dataRecords, $positionRecords, $domainKey)
    {
        list($data, $position, $valueKey, $positionKey) = $this->current($dataRecords, $positionRecords);
        while ($data && $position) {
            if (!isset( $this->valueHash[$domainKey][$valueKey] )) {
                $this->createValue( $domainKey, $valueKey, $positionKey, $data, $position );
            } else {
                $previousPosition=$this->positionHash[$domainKey][$valueKey];
                $detail = $this->redundanceDetail($positionKey);
                throw new GeneralException($valueKey, $previousPosition, $detail, ModelExceptionCode::REDUNDANT_VALUE);
            }
            list( $data, $position, $valueKey, $positionKey ) = $this->next( $dataRecords, $positionRecords );
        }
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    protected function buildEventTag($data, $position)
    {
        list($dataPart, $positionPart, , )=
            $this->current($data,$position);
        while($dataPart && $positionPart){
            $this->createEventTag($dataPart, $positionPart);
            list($dataPart, $positionPart, , )=
                $this->next($data, $position);
        }
    }

    /**
     * @param $key
     */
    private function createDomain($key){
        if(isset($this->domainHash[$key])){
            return;
        }
        $domain=new Domain();
        $domain->setName($key);
        /** @var EntityManagerInterface $em */
        $em=$this->domainRepository->getEntityManager();
        $em->persist($domain);
        $this->domainHash[$key]=$domain;
        $this->valueHash[$key]=[];
        $this->positionHash[$key]=[];
    }


    /**
     * @param $domainKey
     * @param $valueKey
     * @param $valueKeyPosition
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    private function createValue($domainKey, $valueKey, $valueKeyPosition, $data, $position)
    {
        list( , ,$abbr , $abbrPos )=$this->current($data,$position);
        if($abbr!='abbr'){
            throw new GeneralException($abbr, $abbrPos, "expected \"abbr\".", ModelExceptionCode::ABBR);
        }
        list( , , $order, $orderPos)=$this->next($data,$position);
        if($order!='order'){
            throw new GeneralException($order,$orderPos, "expected \"order\".", ModelExceptionCode::ORDER);
        }
        if(isset($this->valueHash[$domainKey][$valueKey])){
            $previousPosition = $this->positionHash[$domainKey][$valueKey];
            $detail=$this->redundanceDetail($position);
            throw new GeneralException($valueKey, $previousPosition,$detail,ModelExceptionCode::REDUNDANT_VALUE);
        }
        $value=new Value();
        $value->setName($valueKey)
                ->setDomain($this->domainHash[$domainKey])
                ->setAbbr($data['abbr'])
                ->setOrd($data['order']);
        /** @var EntityManagerInterface $em */
        $em=$this->valueRepository->getEntityManager();
        $em->persist($value);
        $this->valueHash[$domainKey][$valueKey]=$value;
        $this->positionHash[$domainKey][$valueKey]=$valueKeyPosition;
    }

    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    private function createEventTag($data,$position){
        if(!isset($this->tagHash[$data])){
            $tag=new Tag();
            $tag->setName($data);
            /** @var EntityManagerInterface $em */
            $em=$this->tagRepository->getEntityManager();
            $em->persist($tag);
            $this->tagHash[$data]=$tag;
            $this->positionHash['etag'][$data]=$position;
        } else {
            $priorPosition=$this->positionHash['etag'][$data];
            $detail = $this->redundanceDetail($position);
            throw new GeneralException($data, $priorPosition, $detail, ModelExceptionCode::REDUNDANT_VALUE);
        }
    }

    /**
     * @param $position
     * @return string
     */
    private function redundanceDetail($position){
        $pos=[];
        preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$position, $pos);
        $detail=sprintf('is redundantly defined at row:%s col:%s.', $pos['row'],$pos['col']);
        return $detail;
    }
}