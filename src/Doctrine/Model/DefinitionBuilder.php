<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/2/18
 * Time: 5:17 PM
 */

namespace App\Doctrine\Model;

use App\Doctrine\Builder;
use App\Entity\Configuration\Model as ModelDescription;
use App\Entity\Models\Event;
use App\Entity\Models\Model;
use App\Entity\Models\Player;
use App\Entity\Models\Subevent;
use App\Entity\Models\Tag;
use App\Entity\Models\Value;
use App\Exceptions\ModelExceptionCode;
use App\Exceptions\MissingException;
use App\Exceptions\GeneralException;
use App\Exceptions\RedundanceException;
use App\Repository\Configuration\ModelRepository as ConfigurationRepository;
use App\Repository\Models\DomainRepository;
use App\Repository\Models\EventRepository;
use App\Repository\Models\ModelRepository;
use App\Repository\Models\PlayerRepository;
use App\Repository\Models\SubeventRepository;
use App\Repository\Models\TagRepository;
use App\Repository\Models\ValueRepository;
use App\Subscriber\Status;
use App\Utils\YamlPosition;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

class DefinitionBuilder extends Builder
{

    /**
     * @var ModelRepository
     */
    private $modelRepository;
    /**
     * @var DomainRepository
     */
    private $domainRepository;
    /**
     * @var EventRepository
     */
    private $eventRepository;
    /**
     * @var SubeventRepository
     */
    private $subeventRepository;
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    /** @var Model */
    private $model;

    private $domainHash = [];

    /** @var array */
    private $domainValueHash = [];

    /** @var array */
    private $domainValuePositionHash = [];

    /** @var array */
    private $danceHash = [];

    /** @var array */
    private $dancePositionHash = [];

    /** @var array */
    private $playerHash = [];

    /** @var array  */
    private $playerPositionHash = [];

    /** @var array */
    private $etagHash = [];

    /** @var array */
    private $positionYaml;

    /**
     * @var ValueRepository
     */
    private $valueRepository;


    /**
     * DefinitionBuilder constructor.
     * @param ModelRepository $modelRepository
     * @param DomainRepository $domainRepository
     * @param EventRepository $eventRepository
     * @param SubeventRepository $subeventRepository
     * @param PlayerRepository $playerRepository
     * @param TagRepository $tagRepository
     * @param ValueRepository $valueRepository
     * @param ConfigurationRepository $configurationRepository
     * @param TraceableEventDispatcher|null $eventDispatcher
     */
    public function __construct(
        ModelRepository $modelRepository,
        DomainRepository $domainRepository,
        EventRepository $eventRepository,
        SubeventRepository $subeventRepository,
        PlayerRepository $playerRepository,

        TagRepository $tagRepository,
        ValueRepository $valueRepository,

        ConfigurationRepository $configurationRepository,
        TraceableEventDispatcher $eventDispatcher = null)
    {
        $this->modelRepository = $modelRepository;
        $this->domainRepository = $domainRepository;
        $this->eventRepository = $eventRepository;
        $this->subeventRepository = $subeventRepository;
        $this->playerRepository = $playerRepository;
        //$this->choiceRepository = $choiceRepository;
        $this->tagRepository = $tagRepository;
        $this->valueRepository = $valueRepository;
        //$this->mappingRepository = $mappingRepository;
        $this->configurationRepository = $configurationRepository;
        $this->eventDispatcher = $eventDispatcher;
    }
    /**
     * @param string $yamlTxt
     * @param bool $rebuild
     * @throws MissingException
     * @throws GeneralException
     * @throws RedundanceException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function build(string $yamlTxt, bool $rebuild=false)
    {

        $r = YamlPosition::parse( $yamlTxt );
        $lineCount=YamlPosition::getLineCount();
        $this->sendStatus(Status::COMMENCE,$lineCount);
        while(key($r['data'])=='comment'){
            next($r['data']);next($r['position']);
        }
        $this->buildModel( current($r['data'] ),
                           key( $r['data'] ),    key( $r['position'] ) );
        $this->buildDomainsSection( next( $r['data'] ), next( $r['position'] ),
                                   key( $r['data'] ), key( $r['position']));
        $this->buildDanceSection( next( $r['data'] ), next( $r['position'] ),
                                  key( $r['data'] ), key( $r['position'] ) );
        $this->buildModelValueOwnership();
        $this->buildPlayersSection( next( $r['data'] ), next($r['position']),
                                    key ($r['data']), key($r['position']));
        $this->buildEventTagSection( next( $r['data'] ), next( $r['position']),
                                    key ($r['data']), key($r['position']));

        $this->buildEventsSection( next( $r['data'] ), next($r['position']),
                                   key($r['data']), key($r['position']));
        if(!$rebuild){
            /** @var  ModelDescription $model */
            $model = $this->configurationRepository->findOneBy( ['name' => $this->model->getName()] );
            if ($model) {
                $model->setText( $yamlTxt )
                    ->setUpdatedAt( new \DateTime( 'now' ) );
            } else {
                $model = new ModelDescription();
                $model->setModelId( $this->model->getId() )
                    ->setName( $this->model->getName() )
                    ->setText( $yamlTxt )
                    ->setUpdatedAt( new \DateTime( 'now' ) );
                $emConf = $this->configurationRepository->getEntityManager();
                $emConf->persist( $model );
                $emConf->flush();
            }
            $this->sendStatus( Status::COMPLETE, 100 );
        }
    }

    /**
     * @param string $filename
     * @throws MissingException
     * @throws GeneralException
     * @throws RedundanceException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function buildFromFile(string $filename)
    {
        $yamlTxt=file_get_contents($filename);
        $this->build($yamlTxt);
    }

    /**
     * @throws MissingException
     * @throws GeneralException
     * @throws RedundanceException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function rebuild()
    {
        $records=$this->configurationRepository->findAll();
        /** @var \App\Entity\Configuration\Model $rec */
        foreach($records as $rec){
            $this->build($rec->getText(),true);
        }
    }

    /**
     * @param $file
     */
    public function positionYamlToFile($file)
    {
        file_put_contents( $file, $this->positionYaml );
    }

    /**
     * @param string $dataPart
     * @param string $dataKey
     * @param string $positionKey
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildModel(string $dataPart, string $dataKey, string $positionKey)
    {
        if ($dataKey != 'model') {
            throw new GeneralException($dataKey, $positionKey, 'expected "model"',
                                    ModelExceptionCode::MODEL_EXPECTED);

        }
        $model = $this->modelRepository->findOneBy( ['name' => $dataPart] );
        $em = $this->modelRepository->getEntityManager();
        if(!$model){
            $model = new Model();
            $model->setName( $dataPart );
            $em->persist( $model );
        }
        $em->flush( $model );
        $this->model = $model;
    }

    /**
     * @param array $data
     * @param array $position
     * @param string $dataKey
     * @param string $positionKey
     * @throws MissingException
     * @throws GeneralException
     */
    private function buildDomainsSection(array $data, array $position, string $dataKey, string $positionKey)
    {
        $expectedKeys =  ['style', 'substyle', 'proficiency', 'age', 'type', 'tag'];
        if ($dataKey != 'domains') {
            throw new GeneralException($dataKey, $positionKey, "expected \"domains\"",
                                    ModelExceptionCode::DOMAINS);
        }
        $missingKeys=array_diff($expectedKeys, array_keys($data));
        $keyPositions = array_keys($position);
        if(count($missingKeys)>0){
            throw new MissingException($missingKeys, $keyPositions, ModelExceptionCode::MISSING_DOMAINS);

        }
        $this->iterateAllDomainValues( $data, $position );
    }

    /**
     * @param array $data
     * @param array $position
     * @throws GeneralException
     */
    private function iterateAllDomainValues(array $data, array $position)
    {
        list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->current( $data, $position );
        $acceptedKeys =  ['style', 'substyle', 'proficiency', 'age', 'type', 'tag'];
        while ($dataPart && $positionPart) {
            $domainObj = $this->domainRepository->findOneBy( ['name' => $dataKey] );
            if ($domainObj) {
                $this->domainHash[$dataKey] = $domainObj;
            } else {
                $detail=sprintf('expected "%s".', join('","',$acceptedKeys));
                throw new GeneralException($dataKey,
                                         $positionKey,
                                         $detail,
                                         ModelExceptionCode::DOMAIN);
                }
            $this->iterateDomainValues( $dataPart, $positionPart, $dataKey );
            list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->next( $data, $position );
        }
    }

    /**
     * @param array $data
     * @param array $position
     * @param string $key
     * @throws GeneralException
     */
    private function iterateDomainValues(array $data, array $position, string $key)
    {
        list( $dataPart, $positionPart, ,  ) = $this->current( $data, $position );
        while ($dataPart && $positionPart) {
            $value = $this->valueRepository->findOneBy( ['name' => $dataPart,
                                                         'domain' => $this->domainHash[$key]] );
            if (!$value) {
                $detail=sprintf('is not a valid %s.',$key);
                throw new GeneralException(
                    $dataPart,
                    $positionPart,
                    $detail,
                    ModelExceptionCode::VALUE);
            }
            $this->domainValueHash[$key][$dataPart] = $value;
            $this->domainValuePositionHash[$key][$dataPart] = $positionPart;
            list( $dataPart, $positionPart, ,  ) = $this->next( $data, $position );
        }
    }

    /**
     * @param array $dataPart
     * @param array $positionPart
     * @param string $dataKey
     * @param string $positionKey
     * @throws GeneralException
     */
    private function buildDanceSection(array $dataPart, array $positionPart, string $dataKey, string $positionKey)
    {
        if ($dataKey != 'dances') {
            throw new GeneralException($dataKey, $positionKey,
                                     "expected \"dances\".",
                                     ModelExceptionCode::DANCES);
        }
        $dance = $this->domainRepository->findOneBy( ['name' => 'dance'] );
        $this->domainHash['dance'] = $dance;
        $this->domainValueHash['dance'] = [];
        $this->iterateStylesSubstylesDances( $dataPart, $positionPart );
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildModelValueOwnership()
    {
        foreach (array_keys( $this->domainValueHash ) as $domainKey) {
            foreach ($this->domainValueHash[$domainKey] as $value) {
                $this->model->addValue( $value );
            }
        }
        $this->modelRepository->getEntityManager()->flush();
    }

    /**
     * @param array $data
     * @param array $position
     * @throws GeneralException
     */
    private function iterateStylesSubstylesDances(array $data, array $position)
    {
        list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->current( $data, $position );
        while ($dataPart && $positionPart) {
            if (!isset( $this->domainValueHash['style'][$dataKey] )) {
                /*throw new YamlToModelException("Found \"$dataKey\"",
                                               "which is an undefined dance style.",
                                                $positionKey,
                                                YamlToModelException:);*/
                throw new GeneralException($dataKey, $positionKey,
                                         "is an invalid style.",
                                          ModelExceptionCode::STYLE);
            }
            $this->danceHash[$dataKey] = [];
            $this->dancePositionHash[$dataKey] = [];
            $this->iterateSubstylesDances( $dataPart, $positionPart, $dataKey );
            list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->next( $data, $position );
        }
    }

    /**
     * @param $substylesDances
     * @param $position
     * @param $style
     * @throws GeneralException
     */
    private function iterateSubstylesDances($substylesDances, $position, $style)
    {
        list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->current( $substylesDances, $position );
        while ($dataPart && $positionPart) {
            if (!isset( $this->domainValueHash['substyle'][$dataKey] )) {
                throw new GeneralException($dataKey,$positionKey,
                                        "which is an invalid substyle.",
                                         ModelExceptionCode::SUBSTYLE);
            }
            $this->danceHash[$style][$dataKey] = [];
            $this->dancePositionHash[$style][$dataKey] = [];

            $this->danceHash[$style][$dataKey] = $dataPart;
            $this->dancePositionHash[$style][$dataKey] = $positionPart;

            $this->iterateDances( $dataPart, $positionPart );
            list( $dataPart, $positionPart, $dataKey, $positionKey ) = $this->next( $substylesDances, $position );
        }
    }

    /**
     * @param array $dances
     * @param array $position
     * @throws GeneralException
     */
    private function iterateDances(array $dances, array $position)
    {
        list( $dataPart, $positionPart, , ) = $this->current( $dances, $position );
        while ($dataPart && $positionPart) {
            $value = $this->valueRepository->findOneBy( ['name' => $dataPart,
                                                         'domain' => $this->domainHash['dance']] );
            if (!$value) {
                throw new GeneralException($dataPart, $positionPart,
                                         "is an invalid dance.",
                                         ModelExceptionCode::DANCE);
            }
            if (!isset( $this->domainValueHash['dance'][$dataPart] )) {
                $this->domainValueHash['dance'][$dataPart] = $value;
                $this->domainValuePositionHash['dance'][$dataPart] = $positionPart;
            }
            list( $dataPart, $positionPart, , ) = $this->next( $dances, $position );
        }
    }

    /**
     * @param array $dataPart
     * @param array $positionPart
     * @param string $dataKey
     * @param string $positionKey
     * @throws MissingException
     * @throws GeneralException
     * @throws RedundanceException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildPlayersSection(array $dataPart, array $positionPart, string $dataKey, string $positionKey)
    {
        if ($dataKey != 'players') {
            throw new GeneralException($dataKey,$positionKey,
                                    "expected \"players\"",
                                    ModelExceptionCode::PLAYERS);

        }
        list( $playersPart, $playersPartPosition, , )
            = $this->current( $dataPart, $positionPart);
        while ($playersPart && $playersPartPosition) {
            $this->buildPlayersHash( $playersPart, $playersPartPosition);
            list( $playersPart, $playersPartPosition, , )
                = $this->next( $dataPart, $positionPart );
        }
        $this->playerRepository->getEntityManager()->flush();
    }

    /**
     * @param array $data
     * @param array $position
     * @throws MissingException
     * @throws GeneralException
     * @throws RedundanceException
     * @throws \Doctrine\ORM\ORMException
     */
    private function buildPlayersHash(array $data, array $position)
    {
        $domains=in_array('style',array_keys($data))?['style','proficiency','age','type']:
                            ['substyle','proficiency','age','type'];

        $dataPart=[] ;
        list($tmpDataPart,$tmpPositionPart,$tmpDataKey,$tmpPositionKey)
            =$this->current($data,$position);
        $domainsFound=[];
        while($tmpDataPart && $tmpPositionPart){
            if (!in_array($tmpDataKey,$domains)){
                $detail = sprintf('expected "%s"',join('","',$domains));
                throw new GeneralException($tmpDataKey,$tmpPositionKey,$detail,
                                         ModelExceptionCode::INVALID_DOMAIN_KEY);

            }
            array_push($domainsFound, $tmpDataKey);
            $dataPart[$tmpDataKey]=$tmpDataPart;
            $dataPositionPart[$tmpDataKey]=$tmpPositionPart;
            list($tmpDataPart,$tmpPositionPart,$tmpDataKey,$tmpPositionKey)
                    =$this->next($data,$position);
        }
        $missingDomains = array_diff($domains, array_keys($data));
        if(count($missingDomains)){
            throw new MissingException($missingDomains,array_keys($position),
                ModelExceptionCode::MISSING_KEYS);
        }
        $this->validatePlayersDomainValueHash($data, $position);
        $this->iterateThroughPlayersDomainValues($data, $position);
    }


    /**
     * @param $data
     * @param $position
     * @throws GeneralException
     */
    private function validatePlayersDomainValueHash(array $data, array $position)
    {
        reset($data);reset($position);
        list($dataPart, $positionPart, $domainKey, )=
            $this->current($data,$position);
        while($dataPart && $positionPart){
            list($valuePart,$valuePositionPart, , )=
                $this->current($dataPart, $positionPart);
                while($valuePart && $valuePositionPart){
                if(!isset($this->domainValueHash[$domainKey][$valuePart])){

                    throw new GeneralException($valuePart, $valuePositionPart, "is not a valid $domainKey.",
                                             ModelExceptionCode::INVALID_COMPONENT);
                }
                list($valuePart, $valuePositionPart, , )=
                    $this->next($dataPart, $positionPart);
            }
            list($dataPart, $positionPart, $domainKey, )=
                $this->next($data,$position);
        }
    }

    /**
     * @param $data
     * @param $position
     * @throws RedundanceException
     * @throws \Doctrine\ORM\ORMException
     */
    private function iterateThroughPlayersDomainValues($data, $position)
    {
        reset($data);reset($position);
        $keyedPositions = [];
        list($dataPart ,$positionPart, $dataKey, )=
            $this->current($data,$position);
        while($dataPart && $positionPart){
            $keyedPositions[$dataKey]=$positionPart;
            $keyedPositions[$dataKey]=[];
            list($dataPartValue, $positionPartValue, , )=$this->current($dataPart, $positionPart);
            while($dataPartValue && $positionPartValue){
                $keyedPositions[$dataKey][$dataPartValue]=$positionPartValue;
                list($dataPartValue, $positionPartValue, , )=$this->next($dataPart, $positionPart);
            }
            list($dataPart, $positionPart, $dataKey, )=
                $this->next($data,$position);
        }
        $topCollection=isset($data['style'])?$data['style']:$data['substyle'];
        $genreDomain = isset($data['style'])?'style':'substyle';
        foreach ($topCollection as $genre) {
            foreach ($data['proficiency'] as $proficiency) {
                foreach ($data['age'] as $age) {
                    foreach ($data['type'] as $type) {
                        $lineNumbers = $this->lineNumbers(
                            [$keyedPositions[$genreDomain][$genre],$keyedPositions['proficiency'][$proficiency],
                                $keyedPositions['age'][$age],$keyedPositions['type'][$type]]);
                        $this->buildPlayer( $genre, $proficiency, $age, $type, $lineNumbers);
                    }
                }
            }
        }
    }


    /**
     * @param $styleOrSubstyle
     * @param $proficiency
     * @param $age
     * @param $type
     * @param $lineNumbers
     * @throws RedundanceException
     * @throws \Doctrine\ORM\ORMException
     */
    private function buildPlayer($styleOrSubstyle, $proficiency, $age, $type,$lineNumbers)
    {
        $genreObj = isset($this->domainValueHash['style'][$styleOrSubstyle])?
                          $this->domainValueHash['style'][$styleOrSubstyle]:
                          $this->domainValueHash['substyle'][$styleOrSubstyle];
        $proficiencyObj = $this->domainValueHash['proficiency'][$proficiency];
        $ageObj = $this->domainValueHash['age'][$age];
        $typeObj = $this->domainValueHash['type'][$type];
        $player = new Player();
        $player->setModel( $this->model )
            ->addValue( $genreObj )
            ->addValue( $proficiencyObj )
            ->addValue( $ageObj )
            ->addValue( $typeObj );
        $em = $this->valueRepository->getEntityManager();
        $em->persist( $player );
        if (!isset( $this->playerHash[$styleOrSubstyle] )) {
            $this->playerHash[$styleOrSubstyle] = [];
            $this->playerPositionHash[$styleOrSubstyle]=[];
        }
        if (!isset( $this->playerHash[$styleOrSubstyle][$proficiency] )) {
            $this->playerHash[$styleOrSubstyle][$proficiency] = [];
            $this->playerPositionHash[$styleOrSubstyle][$proficiency] = [];
        }
        if (!isset( $this->playerHash[$styleOrSubstyle][$proficiency][$age] )) {
            $this->playerHash[$styleOrSubstyle][$proficiency][$age] = [];
            $this->playerPositionHash[$styleOrSubstyle][$proficiency][$age] = [];
        }
        if (isset( $this->playerHash[$styleOrSubstyle][$proficiency][$age][$type] )) {
            $classification = join( ', ', [$styleOrSubstyle, $proficiency, $age, $type] );
            $previousLines = $this->playerPositionHash[$styleOrSubstyle][$proficiency][$age][$type];
            throw new RedundanceException('Player redundancy for '.$classification,
                                          $previousLines,$lineNumbers,
                                          ModelExceptionCode::REDUNDANT_COMPONENT);

        }
        $this->playerHash[$styleOrSubstyle][$proficiency][$age][$type] = $player;
        $this->playerPositionHash[$styleOrSubstyle][$proficiency][$age][$type]=$lineNumbers;
    }

    /**
     * @param $dataPart
     * @param $positionPart
     * @param $dataKey
     * @param $positionKey
     * @throws GeneralException
     */
    private function buildEventTagSection($dataPart, $positionPart, $dataKey, $positionKey)
    {

       if($dataKey!='event-tags'){
           throw new GeneralException($dataKey,$positionKey,"expected \"event-tags\"",
                                    ModelExceptionCode::EVENT_TAGS);
       }
        list( $etagDataPart, $etagPositionPart, , ) = $this->current( $dataPart, $positionPart );
        while ($etagDataPart && $etagPositionPart) {
            $tag = $this->tagRepository->findOneBy( ['name' => $etagDataPart] );

            if (!$tag) {
                throw new GeneralException($etagDataPart, $etagPositionPart, "is not valid",
                                        ModelExceptionCode::INVALID_EVENT_TAG);
            }
            $this->etagHash[$etagDataPart] = $tag;

            list( $etagDataPart, $etagPositionPart, ,  ) = $this->next( $dataPart, $positionPart );
        }
    }


    /**
     * @param $data
     * @param $position
     * @param $dataKey
     * @param $dataKeyPosition
     * @throws MissingException
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildEventsSection($data, $position, $dataKey, $dataKeyPosition)
    {

        if($dataKey != 'event-collections'){
            throw new GeneralException($dataKey, $dataKeyPosition, "expected \"event-collections\"",
                                    ModelExceptionCode::EVENT_COLLECTIONS);
        }
        list( $dataPart, $positionPart, , ) = $this->current( $data, $position );
        while ($dataPart && $positionPart) {
            $dataKeys = array_keys( $dataPart );
            $positionKeys = array_keys( $positionPart );
            $this->verifyEventKeys( $dataKeys, $positionKeys );
            $this->buildEventPlayerRelationships( $dataPart, $positionPart );
            list( $dataPart, $positionPart, , ) = $this->next( $data, $position );
        }
    }


    /**
     * @param array $dataKeys
     * @param array $positionKeys
     * @throws MissingException
     * @throws GeneralException
     */
    private function verifyEventKeys(array $dataKeys, array $positionKeys)
    {
        $keys = ['style', 'age', 'proficiency', 'event-tag', 'type'];
        $dataKey = current( $dataKeys );
        $positionKey = current( $positionKeys );
        $foundKeys = [];
        while ($dataKey && $positionKey) {
            if (!in_array( $dataKey, $keys )) {
                $detail = sprintf('expected "%s"',join('","',$keys));
                throw new GeneralException($dataKey, $positionKey, $detail, ModelExceptionCode::INVALID_KEY);
            }
            array_push( $foundKeys, $dataKey );
            $dataKey = next( $dataKeys );
            $positionKey = next( $positionKeys );
        }
        $missingKeys = array_diff( $keys, $foundKeys);
        if (count( $missingKeys )) {
            throw new MissingException($missingKeys, $positionKeys,ModelExceptionCode::MISSING_KEY);
        }
    }

    /**
     * @param array $data
     * @param array $position
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function buildEventPlayerRelationships(array $data, array $position)
    {
        list( $style, $stylePosition ) = $this->findDataAndPositions( 'style', $data, $position );
        if (!isset( $this->domainValueHash['style'][$style] )) {
           throw new GeneralException($style, $stylePosition, "is invalid",ModelExceptionCode::INVALID_STYLE);
        }
        list( $etag, $etagPosition ) = $this->findDataAndPositions( 'event-tag', $data, $position );

        if (!isset( $this->etagHash[$etag] )) {
            throw new GeneralException($etag,$etagPosition,"is invalid",ModelExceptionCode::EVENT_TAG);
        }
        list( $ageData, $agePositions ) = $this->findDataAndPositions( 'age', $data, $position );
        list( $proficiencyData, $proficiencyPositions ) = $this->findDataAndPositions( 'proficiency', $data, $position );
        list( $typeData, $typePositions ) = $this->findDataAndPositions( 'type', $data, $position );
        $styleValue = $this->domainValueHash['style'][$style];
        $etagTag = $this->etagHash[$etag];
        $this->relationshipEventPlayerProficiencyAgeType(
            $ageData, $agePositions,
            $proficiencyData, $proficiencyPositions,
            $typeData, $typePositions,
            $styleValue, $etagTag );
    }


    /**
     * @param array $ageData
     * @param array $agePositions
     * @param array $proficiencyData
     * @param array $proficiencyPositions
     * @param array $typeData
     * @param array $typePositions
     * @param Value $styleValue
     * @param Tag $etag
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function relationshipEventPlayerProficiencyAgeType(array $ageData, array $agePositions,
                                                               array $proficiencyData, array $proficiencyPositions,
                                                               array $typeData, array $typePositions,
                                                               Value $styleValue, Tag $etag)
    {

        list( $eventProficiencyData, $eventProficiencyPosition,$eventTag, $eventTagPosition )
            = $this->current( $proficiencyData, $proficiencyPositions );
        while ($eventProficiencyData && $eventProficiencyPosition) {
            if (!isset( $this->domainValueHash['tag'][$eventTag] )) {
                throw new GeneralException($eventTag,$eventTagPosition,'is invalid tag',ModelExceptionCode::INVALID_TAG);
            }
            $tagValue = $this->domainValueHash['tag'][$eventTag];
            $this->drillDownEventProficiency( $ageData, $agePositions,
                $eventProficiencyData, $eventProficiencyPosition,
                $typeData, $typePositions,
                $styleValue, $etag, $tagValue );
            list( $eventProficiencyData, $eventProficiencyPosition, $eventTag, $eventTagPosition)
                = $this->next( $proficiencyData, $proficiencyPositions );
        }

    }

    /**
     * @param $ageData
     * @param $agePosition
     * @param $eventProficiencyData
     * @param $eventProficiencyPosition
     * @param $typeData
     * @param $typePositions
     * @param Value $styleValue
     * @param Tag $etag
     * @param Value $tagValue
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function drillDownEventProficiency($ageData, $agePosition,
                                               $eventProficiencyData, $eventProficiencyPosition,
                                               $typeData, $typePositions,
                                               Value $styleValue, Tag $etag, Value $tagValue)
    {
        list( $eligibleDancesData, $eligibleDancesPosition, $eventProficiencyKey, $eventProficiencyKeyPosition ) =
            $this->current( $eventProficiencyData, $eventProficiencyPosition );
        while ($eligibleDancesData && $eligibleDancesPosition) {
            if (!isset( $this->domainValueHash['proficiency'][$eventProficiencyKey] )) {
                throw new GeneralException($eventProficiencyKey, $eventProficiencyKeyPosition, 'is invalid proficiency',
                                        ModelExceptionCode::INVALID_PROFICIENCY);
            }
            $eventProficiencyValue = $this->domainValueHash['proficiency'][$eventProficiencyKey];
            $this->drillDownEventAge( $ageData, $agePosition,
                $eligibleDancesData, $eligibleDancesPosition,
                $typeData, $typePositions,
                $styleValue, $etag, $tagValue,
                $eventProficiencyValue );
            list( $eligibleDancesData, $eligibleDancesPosition, $eventProficiencyKey, $eventProficiencyKeyPosition )
                = $this->next( $eventProficiencyData, $eventProficiencyPosition );
        }
    }


    /**
     * @param $ageData
     * @param $agePosition
     * @param $eligibleDancesData
     * @param $eligibleDancesPositions
     * @param $typeData
     * @param $typePositions
     * @param Value $styleValue
     * @param Tag $etag
     * @param Value $tagValue
     * @param Value $eventProficiencyValue
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function drillDownEventAge($ageData, $agePosition,
                                       $eligibleDancesData, $eligibleDancesPositions,
                                       $typeData, $typePositions,
                                       Value $styleValue, Tag $etag, Value $tagValue,
                                       Value $eventProficiencyValue)
    {
        list( $playersAgeData, $playersAgePosition, $eventAge, $eventAgePosition )
            = $this->current( $ageData, $agePosition );
        while ($playersAgeData && $playersAgePosition) {
            if (!isset( $this->domainValueHash['age'][$eventAge] )) {
                throw new GeneralException($eventAge,$eventAgePosition,"is an invalid age",
                                        ModelExceptionCode::INVALID_AGE);
            }
            $eventAgeValue = $this->domainValueHash['age'][$eventAge];
            $this->drillDownEventType( $playersAgeData, $playersAgePosition,
                $eligibleDancesData, $eligibleDancesPositions,
                $typeData, $typePositions,
                $styleValue, $etag, $tagValue,
                $eventProficiencyValue,
                $eventAgeValue );
            list( $playersAgeData, $playersAgePosition, $eventAge, $eventAgePosition )
                = $this->next( $ageData, $agePosition );
        }

    }

    /**
     * @param $playersAgeData
     * @param $playersAgePosition
     * @param $eligibleDancesData
     * @param $eligibleDancesPosition
     * @param $typeData
     * @param $typePositions
     * @param Value $styleValue
     * @param Tag $etag
     * @param Value $tagValue
     * @param Value $eventProficiencyValue
     * @param Value $eventAgeValue
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function drillDownEventType($playersAgeData, $playersAgePosition,
                                        $eligibleDancesData, $eligibleDancesPosition,
                                        $typeData, $typePositions,
                                        Value $styleValue, Tag $etag, Value $tagValue,
                                        Value $eventProficiencyValue,
                                        Value $eventAgeValue)
    {
        list( $playersTypeData, $playersTypePosition, $eventType, $eventTypePosition ) =
            $this->current( $typeData, $typePositions );
        while ($playersTypeData && $playersTypePosition) {
            if (!isset( $this->domainValueHash['type'][$eventType] )) {
                throw new GeneralException($eventType,$eventTypePosition,"is invalid type",
                                        ModelExceptionCode::INVALID_TYPE);
            }
            $eventTypeValue = $this->domainValueHash['type'][$eventType];
            $this->drillDownPlayers( $playersAgeData, $playersAgePosition,
                $eligibleDancesData, $eligibleDancesPosition,
                $playersTypeData, $playersTypePosition,
                $styleValue, $etag, $tagValue,
                $eventProficiencyValue,
                $eventAgeValue,
                $eventTypeValue );
            list( $playersTypeData, $playersTypePosition, $eventType, $eventTypePosition ) =
                $this->next( $typeData, $typePositions );
        }
    }

    /**
     * @param $playersAgeData
     * @param $playersAgePosition
     * @param $eligibleDancesData
     * @param $eligibleDancesPosition
     * @param $playersTypeData
     * @param $playersTypePosition
     * @param Value $styleValue
     * @param Tag $etag
     * @param Value $tagValue
     * @param Value $eventProficiencyValue
     * @param Value $eventAgeValue
     * @param Value $eventTypeValue
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function drillDownPlayers($playersAgeData, $playersAgePosition,
                                      $eligibleDancesData, $eligibleDancesPosition,
                                      $playersTypeData, $playersTypePosition,
                                      Value $styleValue, Tag $etag, Value $tagValue,
                                      Value $eventProficiencyValue,
                                      Value $eventAgeValue,
                                      Value $eventTypeValue)
    {
        list( $playersProficiencyData, $playersProficiencyPosition, $eligibleKey, $eligibleKeyPosition ) =
            $this->current( $eligibleDancesData, $eligibleDancesPosition );
        if ($eligibleKey != 'eligible') {
            throw new GeneralException($eligibleKey, $eligibleKeyPosition, "expected \"eligible\"",
                                     ModelExceptionCode::ELIGIBLE);
        }
        list( $substyleDances, $substyleDancesPosition, $eventsKey, $eventsKeyPosition ) =
            $this->next( $eligibleDancesData, $eligibleDancesPosition );
        if ($eventsKey != 'events' && $eventsKey != 'single-event') {
            throw new GeneralException($eventsKey,$eventsKeyPosition,'expected "events" or "single-event"',
                                    ModelExceptionCode::EVENTS);
        }
        switch ($eventsKey) {
            case 'single-event':
                $this->createSingleEvent(
                    $playersProficiencyData, $playersProficiencyPosition,
                    $playersAgeData, $playersAgePosition,
                    $playersTypeData, $playersTypePosition,
                    $substyleDances, $substyleDancesPosition,
                    $styleValue, $etag, $tagValue,
                    $eventProficiencyValue, $eventAgeValue,
                    $eventTypeValue );
                break;
            case 'events':
                $this->createMultipleEvents(
                    $playersProficiencyData, $playersProficiencyPosition,
                    $playersAgeData, $playersAgePosition,
                    $playersTypeData, $playersTypePosition,
                    $substyleDances, $substyleDancesPosition,
                    $styleValue, $etag, $tagValue,
                    $eventProficiencyValue, $eventAgeValue,
                    $eventTypeValue );
        }
    }

    /**
     * @param $playersProficiencyData
     * @param $playersProficiencyPosition
     * @param $playersAgeData
     * @param $playersAgePosition
     * @param $playersTypeData
     * @param $playersTypePosition
     * @param $substyleDances
     * @param $substyleDancesPosition
     * @param Value $styleValue
     * @param Tag $eventTag
     * @param Value $tagValue
     * @param Value $eventProficiencyValue
     * @param Value $eventAgeValue
     * @param Value $eventTypeValue
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createSingleEvent($playersProficiencyData, $playersProficiencyPosition,
                                       $playersAgeData, $playersAgePosition,
                                       $playersTypeData, $playersTypePosition,
                                       $substyleDances, $substyleDancesPosition,
                                       Value $styleValue, Tag $eventTag, Value $tagValue,
                                       Value $eventProficiencyValue, Value $eventAgeValue,
                                       Value $eventTypeValue)
    {
        $subStyleDanceCollections = [];
        list( $dances, $dancesPosition, $substyle, $substylePosition)
                = $this->current( $substyleDances, $substyleDancesPosition );
        while ($dances && $dancesPosition) {
            if(is_array($dances[0])){
                $token=$dances[0][0];
                $position=$dancesPosition[0][0];
                throw new GeneralException($token, $position, 'expected single bracket "[" before token.',
                                        ModelExceptionCode::SINGLE_BRACKET);
            }
            if(!isset($this->domainValueHash['substyle'][$substyle]) ||
                !isset($this->danceHash[$styleValue->getName()][$substyle])){
                throw new GeneralException($substyle, $substylePosition,"is invalid substyle.",
                                        ModelExceptionCode::INVALID_SUBSTYLE);
            }
            /** @var ArrayCollection */
            $collection= new ArrayCollection();
            list($singleDance, $singleDancePosition, , ) = $this->current($dances,$dancesPosition);
            while($singleDance && $singleDancePosition){
                if(!isset($this->domainValueHash['dance'][$singleDance])){
                    throw new GeneralException($singleDance, $singleDancePosition, "is an invalid dance",
                                            ModelExceptionCode::INVALID_DANCE);
                }
                $collection->add($this->domainValueHash['dance'][$singleDance]);
                list($singleDance, $singleDancePosition, , )= $this->next($dances, $dancesPosition);
            }
            $subStyleDanceCollections[$substyle]=$collection;
            list( $dances, $dancesPosition, $substyle, $substylePosition)
                = $this->next( $substyleDances, $substyleDancesPosition );
        }
        $this->createEventAndSubevents(
            $playersProficiencyData, $playersProficiencyPosition,
            $playersAgeData, $playersAgePosition,
            $playersTypeData, $playersTypePosition,
            $styleValue, $eventTag, $tagValue,
            $eventProficiencyValue, $eventAgeValue,
            $eventTypeValue,$subStyleDanceCollections);
    }

    /**
     * @param $playersProficiencyData
     * @param $playersProficiencyPosition
     * @param $playersAgeData
     * @param $playersAgePosition
     * @param $playersTypeData
     * @param $playersTypePosition
     * @param Value $styleValue
     * @param Tag $eventTag
     * @param Value $tagValue
     * @param Value $eventProficiencyValue
     * @param Value $eventAgeValue
     * @param Value $eventTypeValue
     * @param array $subStyleDanceCollections
     * @throws GeneralException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createEventAndSubevents(
        $playersProficiencyData, $playersProficiencyPosition,
        $playersAgeData, $playersAgePosition,
        $playersTypeData, $playersTypePosition,
        Value $styleValue, Tag $eventTag, Value $tagValue,
        Value $eventProficiencyValue, Value $eventAgeValue,
        Value $eventTypeValue, array $subStyleDanceCollections)
    {
        $emEvent = $this->eventRepository->getEntityManager();
        $genreValue=$styleValue;
        if(count($subStyleDanceCollections)==1){
             $key=key($subStyleDanceCollections);
             $genreValue = $this->domainValueHash['substyle'][$key];
        }
        $event=$this->createEvent($styleValue,$subStyleDanceCollections,
            $eventProficiencyValue,$eventAgeValue,
            $eventTypeValue,$tagValue,$eventTag);
        $this->addEventToPlayerCollection(
            $genreValue,
            $playersProficiencyData, $playersProficiencyPosition,
            $playersAgeData, $playersAgePosition,
            $playersTypeData, $playersTypePosition,
            $event);
        foreach($subStyleDanceCollections as $substyleName=>$danceCollection){
            $subevent=$this->createSubevent($substyleName,$danceCollection,
                                            $eventProficiencyValue,$eventAgeValue,
                                            $eventTypeValue);
            $subevent->setEvent($event);
        }
        $emEvent->flush();
    }

    /**
     * @param Value $style
     * @param array $subStyleDanceCollections
     * @param Value $proficiency
     * @param Value $age
     * @param Value $type
     * @param Value $tag
     * @param Tag $eventTag
     * @return Event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createEvent(Value $style, array $subStyleDanceCollections,
                                Value $proficiency, Value $age, Value $type, Value $tag,
                                Tag $eventTag) : Event
    {
        $em = $this->eventRepository->getEntityManager();
        $event = new Event();
        $event->addValue($style)
            ->addValue($proficiency)
            ->addValue($age)
            ->addValue($type)
            ->addValue($tag)
            ->setTag($eventTag)
            ->setModel($this->model);
        /** @var ArrayCollection $collection */
        foreach($subStyleDanceCollections as $collection){
            foreach($collection->toArray() as $dance){
                $event->addValue($dance);
            }
        }
        $em->persist($event);
        $em->flush();
        return $event;


    }

    /**
     * @param string $substyleName
     * @param ArrayCollection $danceCollection
     * @param Value $proficiency
     * @param Value $age
     * @param Value $type
     * @return Subevent
     * @throws \Doctrine\ORM\ORMException
     */
    public function createSubevent(string $substyleName, ArrayCollection $danceCollection,
                                   Value $proficiency, Value $age, Value $type)  : Subevent
    {
        $em=$this->subeventRepository->getEntityManager();
        $substyle = $this->domainValueHash['substyle'][$substyleName];
        $subevent = new Subevent();
        $subevent->addValue($substyle)
                    ->addValue($proficiency)
                    ->addValue($age)
                    ->addValue($type);
        foreach ($danceCollection->toArray() as $dance) {
            $subevent->addValue($dance);
        }
        $em->persist( $subevent );
        return $subevent;
    }

    /**
     * @param $playersProficiencyData
     * @param $playersProficiencyPosition
     * @param $playersAgeData
     * @param $playersAgePosition
     * @param $playersTypeData
     * @param $playersTypePosition
     * @param $substyleDances
     * @param $substyleDancesPosition
     * @param Value $styleValue
     * @param Tag $etag
     * @param Value $tagValue
     * @param Value $eventProficiencyValue
     * @param Value $eventAgeValue
     * @param Value $eventTypeValue
     * @throws GeneralException
     * @throws \Exception
     */

    public function createMultipleEvents($playersProficiencyData, $playersProficiencyPosition,
                                        $playersAgeData, $playersAgePosition,
                                        $playersTypeData, $playersTypePosition,
                                        $substyleDances, $substyleDancesPosition,
                                        Value $styleValue, Tag $etag, Value $tagValue,
                                        Value $eventProficiencyValue, Value $eventAgeValue,
                                        Value $eventTypeValue )
    {
        list( $substyleDanceCollections, $substyleDanceCollectionsPosition, $substyle, $substylePosition )
                = $this->current( $substyleDances, $substyleDancesPosition );
        while ($substyleDanceCollections && $substyleDanceCollectionsPosition) {
            if(!isset($this->domainValueHash['substyle'][$substyle]) ||
                !isset($this->danceHash[$styleValue->getName()][$substyle])){
                throw new GeneralException($substyle, $substylePosition,'invalid style',
                                         ModelExceptionCode::SUBSTYLE);
            }
            if(is_scalar($substyleDanceCollections[0])){
                $token = $substyleDanceCollections[0];
                $position = $substyleDanceCollectionsPosition[0];
                throw new GeneralException($token,$position,
                                        'there must be "[[" and matched brackets',
                                         ModelExceptionCode::DOUBLE_BRACKET);
            }
            list($danceCollection, $danceCollectionPosition, )=
                $this->current($substyleDanceCollections, $substyleDanceCollectionsPosition);
            while($danceCollection && $danceCollectionPosition){
                //var_dump($danceCollection, $substyle);die;
                /** @var ArrayCollection $collection */
                $collection = new ArrayCollection();
                list($dance,$dancePosition, , ) = $this->current($danceCollection, $danceCollectionPosition);
                while($dance && $dancePosition){
                    if(!isset($this->domainValueHash['dance'][$dance])){
                        throw new GeneralException($dance, $dancePosition, "is an invalid dance",
                                                ModelExceptionCode::INVALID_DANCE);
                    }
                    $collection->add($this->domainValueHash['dance'][$dance]);
                    list($dance,$dancePosition, , ) = $this->next($danceCollection,$danceCollectionPosition);
                }
                $this->createEventAndSubevents(
                                           $playersProficiencyData, $playersProficiencyPosition,
                                           $playersAgeData, $playersAgePosition,
                                           $playersTypeData, $playersTypePosition,
                                           $styleValue, $etag, $tagValue,
                                           $eventProficiencyValue, $eventAgeValue,
                                           $eventTypeValue,[$substyle=>$collection]);
                list($danceCollection, $danceCollectionPosition, , )=
                    $this->next($substyleDanceCollections, $substyleDanceCollectionsPosition);
            }
            list( $substyleDanceCollections, $substyleDanceCollectionsPosition, $substyle, )
                    = $this->next( $substyleDances, $substyleDancesPosition );
        }
    }

    /**
     * @param Value $genre
     * @param $playerProficiencyData
     * @param $playerProficiencyPosition
     * @param $playerAgeData
     * @param $playerAgePosition
     * @param $playerTypeData
     * @param $playerTypePosition
     * @param Event $event
     * @throws GeneralException
     */
    private function addEventToPlayerCollection( Value $genre,
                                            $playerProficiencyData, $playerProficiencyPosition,
                                            $playerAgeData, $playerAgePosition,
                                            $playerTypeData, $playerTypePosition,
                                            Event $event)
    {
        
        $proficiencyPosition = current($playerProficiencyPosition);
        foreach($playerProficiencyData as $proficiency){
            $agePosition = current($playerAgePosition);
            foreach($playerAgeData as $age){
                $typePosition = current($playerTypePosition);
                foreach($playerTypeData as $type){
                    $player = $this->retrievePlayer($genre, $proficiency, $age, $type,
                                                    $proficiencyPosition, $agePosition, $typePosition);
                    $player->getEvent()->add($event);
                    $typePosition=next($playerTypePosition);
                }
                reset($playerTypePosition);
                $agePosition = next($playerAgePosition);
            }
            reset($playerAgePosition);
            $proficiencyPosition = next($playerProficiencyPosition);
        }
    }

    /**
     * @param Value $genre
     * @param $proficiency
     * @param $age
     * @param $type
     * @param $proficiencyPosition
     * @param $agePosition
     * @param $typePosition
     * @return Player
     * @throws GeneralException
     */
    private function retrievePlayer(Value $genre, $proficiency, $age, $type,
                                    $proficiencyPosition, $agePosition, $typePosition):Player
    {
        $genreName= $genre->getName();
        if(!isset($this->playerHash[$genreName][$proficiency])){
            throw new GeneralException($proficiency, $proficiencyPosition, 'no metadata definition for this player proficiency.',
                                    ModelExceptionCode::INVALID_PROFICIENCY);
        }
        if(!isset($this->playerHash[$genreName][$proficiency][$age])){
            throw new GeneralException($age, $agePosition, 'no metadata definition for this player age.',
                                    ModelExceptionCode::INVALID_AGE);
        }
        if(!isset($this->playerHash[$genreName][$proficiency][$age][$type])){
            throw new GeneralException($type, $typePosition, 'no metadata definition for this player type',
                                    ModelExceptionCode::INVALID_TYPE);
        }
        return $this->playerHash[$genreName][$proficiency][$age][$type];
    }


    /**
     * @param $key
     * @param $data
     * @param $position
     * @return array
     */
    private function findDataAndPositions(string $key, array $data, array $position):array
    {
        list($dataPart, $positionPart, $dataKey, )=$this->current($data,$position);
        while($dataPart && $positionPart){
            if($dataKey==$key){
                return [$dataPart, $positionPart];
            }
            list($dataPart, $positionPart, $dataKey, ) = $this->next($data, $position);
        }
        return [];
    }
}