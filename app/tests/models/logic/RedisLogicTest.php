<?php
namespace app\tests\models\logic;
use PHPUnit\Framework\TestCase;
use app\models\logic\RedisLogic;
use app\models\data\RedisData;

class RedisLogicTest extends TestCase{
    public function testRedisMuti()
    {
        $muti = array(
            'mock1',
            'mock2'
        );
        $dataMock = $this->getMockBuilder(RedisData::class)
                      ->disableOriginalConstructor()
                      ->setMethods(['getMuti'])
                      ->getMock();
        
        $dataMock->expects($this->once())
                 ->method('getMuti')
                 ->willReturn($muti);
             
                 
        RedisData::set($dataMock);
        $logic = RedisLogic::getInstance();
        $data = $logic->redisMuti();
        $this->assertEquals($muti, $data);
    }
}