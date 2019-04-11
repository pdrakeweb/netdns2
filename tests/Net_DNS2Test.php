<?php

require_once 'Net/DNS2.php';

class PHPUnitUtil
{
    public static function callMethod($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}

abstract class Net_DNS2Test extends PHPUnit_Framework_TestCase
{
    protected $type = 'A';
    protected $class = 'IN';
    protected $name = 'mydomain.com';
    protected $timeout = 5;
    protected $use_tcp = NULL;
    protected $randomize_ns = FALSE;

    /**
     * Happy path test.
     * 
     * @return Net_DNS2_Packet_Response
     */
    public function testSendPacket()
    {
        $nameservers = array('8.8.8.8', '8.8.4.4');
        $result = $this->sendPacket($nameservers);

        $this->assertInstanceOf('Net_DNS2_Packet_Response', $result);
        $this->assertResultValues($result);

        return $result;
    }

    /**
     * Test the result when one nameservers is unreachable.
     * 
     * @return Net_DNS2_Packet_Response
     */
    public function testSendPacketOneInvalidDNSServer()
    {
        $nameservers = array('1.2.3.4', '8.8.8.8');
        $result = $this->sendPacket($nameservers);

        $this->assertInstanceOf('Net_DNS2_Packet_Response', $result);
        $this->assertResultValues($result);

        return $result;
    }

    /**
     * Test the result when no valid nameservers are provided.
     * 
     * @expectedException Net_DNS2_Exception
     * @expectedExceptionMessage every name server provided has failed
     */
    public function testSendPacketNULLDNSServer()
    {
        $this->timeout = 1;
        $nameservers = array(NULL);
        $result = $this->sendPacket($nameservers);
    }

    /**
     * Test the result when unreachable nameservers are provided.
     * 
     * @expectedException Net_DNS2_Exception
     * @expectedExceptionMessage every name server provided has failed
     */
    public function testSendPacketInvalidDNSServersOnly()
    {
        $this->timeout = 1;
        $nameservers = array('1.2.3.4', '5.6.7.8');
        $this->sendPacket($nameservers);
    }

    /**
     * Assert the values returned in the result object.
     * 
     * @param unknown $result
     */
    protected function assertResultValues($result)
    {
        // Assert Question values.
        $question = $result->question[0];
        $this->assertInstanceOf('Net_DNS2_Question', $question);
        $this->assertSame($this->name, $question->qname);
        $this->assertSame($this->type, $question->qtype);
        $this->assertSame($this->class, $question->qclass);

        // Assert Answer values.
        $answer = $result->answer[0];
        $this->assertInstanceOf('Net_DNS2_RR_'.$this->type, $answer);
        $this->assertSame($this->name, $answer->name);
        $this->assertSame($this->type, $answer->type);
        $this->assertSame($this->class, $answer->class);
    }

    /**
     * Send a Packet to a Resolver using the provided nameservers.
     *
     * @param Array $nameservers
     * @return mixed
     */
    private function sendPacket($nameservers)
    {
        $packet_request = $this->createPacketRequest();
        $resolver = $this->createResolver($nameservers);
        $result = PHPUnitUtil::callMethod(
            $resolver,
            'sendPacket',
            array($packet_request, $this->use_tcp)
            );
        return $result;
    }

    /**
     * Create a Resolver with the given nameservers.
     * 
     * @param Array $nameservers
     * @return Net_DNS2_Resolver
     */
    private function createResolver($nameservers)
    {
        $resolver = new Net_DNS2_Resolver(array('nameservers' => $nameservers));
        $resolver->timeout = $this->timeout;
        $resolver->ns_random = $this->randomize_ns;
        return $resolver;
    }

    /**
     * Create a Packet Request for use in tests.
     * 
     * @return Net_DNS2_Packet_Request
     */
    private function createPacketRequest()
    {
        return new Net_DNS2_Packet_Request($this->name, $this->type, $this->class);
    }
}

class NET_DNS2DefaultsTest extends Net_DNS2Test
{
    public function testSendPacket()
    {
        $result = parent::testSendPacket();
        $this->assertSame('8.8.8.8', $result->answer_from);
    }

    protected function assertResultValue($result)
    {
        parent::assertResultValue($result);
        $answer_is_ip = filter_var($result->answer[0]->address, FILTER_VALIDATE_IP) !== FALSE;
        $this->assertTrue($answer_is_ip);
    }
}

class NET_DNS2UseTCPTest extends NET_DNS2DefaultsTest
{
    protected $use_tcp = TRUE;
}

class NET_DNS2RandomizeTest extends Net_DNS2Test
{
    protected $randomize_ns = TRUE;

    public function testSendPacket()
    {
        $result = parent::testSendPacket();
        $this->assertContains($result->answer_from, Array('8.8.8.8', '8.8.4.4'));
    }
}

class NET_DNS2CNAMETest extends Net_DNS2Test
{
    protected $type = 'CNAME';
    protected $name = 'www.mydomain.com';

    protected function assertResultValues($result)
    {
        parent::assertResultValues($result);
        $this->assertSame('mydomain.com', $result->answer[0]->cname);
    }
}

class NET_DNS2TXTTest extends Net_DNS2Test
{
    protected $type = 'TXT';
    protected $name = 'mydomain.com';

    protected function assertResultValues($result)
    {
        $expected_value = 'Yv=spf1 ip4:38.113.1.0/24 ip4:38.113.20.0/24 ip4:12.45.243.128/26 ip4:65.254.224.0/19 ?all';
        parent::assertResultValues($result);
        $this->assertSame($expected_value, $result->answer[0]->rdata);
    }
}
