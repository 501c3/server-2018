<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/26/18
 * Time: 5:32 PM
 */

namespace App\Utils;
use App\Exceptions\GeneralException;
use Symfony\Component\Yaml\Yaml;

class YamlPosition
{
    private static $positions;

    private static $lineCount;

    /**
     * @param string $string
     * @return array
     * @throws \Exception
     */
    public static function parse(string $string){
        $data=Yaml::parse($string);
        if(is_null($data)) {
            throw new \Exception("No yaml string to parse", 255);
        }
        $rowColumns=self::rowColumn($string);
        $positions=Yaml::parse($rowColumns);
        return ['data'=>$data, 'position'=>$positions];
    }

    public static function getLineCount()
    {
        return self::$lineCount;
    }

    /**
     * @return mixed|null
     */
    public static function positionYaml()
    {
        return self::$positions;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function rowColumn(string $string)
    {
        /** @var array $lines */
        $lines=mbsplit('\n',$string);
        self::$lineCount=count($lines);
        $row=0;
        $positions = [];
        foreach($lines as $line)
        {
            $row++;
            array_push($positions, self::position($row, $line));
        }
        self::$positions=join("\n",$positions);
        return self::$positions;
    }

    public static function position(int $row, $string)
    {
        $len=strlen($string);
        $col=0;
        $array=[];
        $match=[];
        $char=substr($string,$col,1);
        $rest=substr($string,$col);
        while($col<$len){
            if(in_array($char, [' ',',','{','}','[',']','-',':'])){
                $col++;
                array_push($array,$char);
            }elseif (preg_match('/(\<|\/)?\w+((\s|\-|\.|\@|\/|\ )\w+)*(\+|\-)?/',$rest, $match)) {
                $size = strlen( $match[0] );
                $position = sprintf( 'R%dC%d',$row,$col+1);
                array_push( $array, $position );
                $col += $size;
            }else{
                $col++;
            }
            $char=substr($string,$col,1);
            $rest=substr($string,$col);
        }
        return join('',$array);
    }

    public static function validEmail($email){
        return preg_match('/\w+((\.)\w+)*\@\w+\.\w{2,3}/',$email);
    }
}