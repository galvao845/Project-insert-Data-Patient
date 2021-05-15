<?php
namespace tests\Zeedhi\Framework\Report\Strategy;

use Zeedhi\Framework\Report\Strategy\QR2Strategy;

class QR2StrategyTest extends \PHPUnit\Framework\TestCase {

    /** @var QR2Strategy */
    protected $qr2Strategy;

    public function testSoapClientCreation() {
        $options = array(
            'proxy_host'     => 'localhost',
            'proxy_port'     => 8080,
            'proxy_login'    => 'pauloneto',
            'proxy_password' => 'm4a4ak47'
        );

        $qr2Type = 'pdf';
        $baseUrl = 'http://localhost/';
        $serverUrl = 'test';
        $port = 80;
        $qr2Strategy = $this->getMockBuilder(QR2Strategy::class)
                            ->setMethods(array('createSoapClient'))
                            ->setConstructorArgs(array($qr2Type, $baseUrl, $serverUrl, $port, 'POR', $options))
                            ->getMock();

        $soapClient = $this->getMockBuilder(\SoapClient::class)
                           ->setMethods(array('getReport'))
                           ->disableOriginalConstructor()
                           ->getMock();

        $report = new \stdClass();
        $report->GetReportResult = 'reportFile';
        $soapClient->expects($this->once())
                   ->method('getReport')
                   ->will($this->returnValue($report));

        $expectedUrl = 'http://localhost/test';
        $qr2Strategy->expects($this->once())
                    ->method('createSoapClient')
                    ->with($expectedUrl, $options)
                    ->will($this->returnValue($soapClient));

        $qr2Strategy->createRemoteReport('reportName', array());
    }

}
