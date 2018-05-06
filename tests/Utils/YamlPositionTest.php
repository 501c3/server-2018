<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/26/18
 * Time: 7:06 PM
 */

namespace Tests\Utils;

use App\Utils\YamlPosition;
use PHPUnit\Framework\TestCase;

class YamlPositionTest extends TestCase
{
    //    BY_TEN      = '0        10        20        30        40        50        60        70'
    //    RULER       = '1234567890123456789012345678901234567890123456789012345678901234567890123456789'
    const TEST_LINE_A = 'test-line-a: {substyle: [[dance], [dance], [dance], [dance], [dance]]}';
    const POSITION_A = 'R1C1: {R1C15: [[R1C27], [R1C36], [R1C45], [R1C54], [R1C63]]}';
    const TEST_LINE_B = '<test-line-b+: {abbr: tb+, order: 3}';
    const POSITION_B  = 'R1C1: {R1C17: R1C23, R1C28: R1C35}';


    /**
     * @expectedException  \Exception
     */
    public function testEmptyString()
    {
        YamlPosition::parse( "" );
    }

     public function testParseLineA()
    {
        $result = YamlPosition::position( 1, self::TEST_LINE_A );
        $this->assertSame( self::POSITION_A, $result );
    }

    public function testParseLineB()
    {
        $result = YamlPosition::position( 1, self::TEST_LINE_B );
        $this->assertSame(self::POSITION_B, $result);
    }

    public function testParsePrimitivesCorrect()
    {
        $realpath=realpath( __DIR__ . '/../Source/' );
        $file=$realpath.'/primitives-correct.yml';
        $primitivesText=file_get_contents($file);
        $result=YamlPosition::rowColumn($primitivesText);
        $this->assertGreaterThan(200, strlen($result));
    }

 }