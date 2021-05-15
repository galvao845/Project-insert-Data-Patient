<?php
namespace tests\Zeedhi\Framework\Report\Strategy;

use Zeedhi\Framework\Report\Strategy\BirtStrategy;

class BirtStrategyTest extends \PHPUnit\Framework\TestCase {

    /** @var BirtStrategy */
    protected $birtStrategy;

    protected function setUp() {
        $this->birtStrategy = new BirtStrategy(
            "",
            "/report",
            "",
            "http://192.168.120.248:8080",
            BirtStrategy::DYNAMIC_REL,
            BirtStrategy::FORMAT_HTML,
            "run"
        );
    }

    public function testSimpleReportCall() {
        $expectedUrl = "http://192.168.120.248:8080/birt-viewer/run?__report=%2Freport%2FCOC00991.rptdesign&__format=html&__svg=false&__locale=pt_BR&__timezone=America%2FSao_Paulo&P_VERSAO=5.07.006+WEB&P_NRORG=1&P_CDFILIAL=&P_ZEBRADO=S&IMG_SRC=&PATH=";
        $actualUrl = $this->birtStrategy->createRemoteReport("COC00991", array(
            "P_NRORG" => 1,
            "P_CDFILIAL" => null,
            "P_ZEBRADO" => "S"
        ));

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testReportCallChangingFormat() {
        $expectedUrl = "http://192.168.120.248:8080/birt-viewer/run?__report=%2Freport%2FCOC00991.rptdesign&__format=pdf&__svg=false&__locale=pt_BR&__timezone=America%2FSao_Paulo&P_VERSAO=5.07.006+WEB&P_NRORG=1&P_CDFILIAL=&P_ZEBRADO=S&IMG_SRC=&PATH=";
        $actualUrl = $this->birtStrategy->createRemoteReport("COC00991", array(
            "P_NRORG" => 1,
            "P_CDFILIAL" => null,
            "P_ZEBRADO" => "S",
            "__format"  => "pdf"
        ));

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testReportCallChangingLocaleAndTimezone() {
        $this->birtStrategy->setReportLocale('en');
        $this->birtStrategy->setReportTimezone('America/New_York');
        $expectedUrl = "http://192.168.120.248:8080/birt-viewer/run?__report=%2Freport%2FCOC00991.rptdesign&__format=pdf&__svg=false&__locale=en&__timezone=America%2FNew_York&P_VERSAO=5.07.006+WEB&P_NRORG=1&P_CDFILIAL=&P_ZEBRADO=S&IMG_SRC=&PATH=";
        $actualUrl = $this->birtStrategy->createRemoteReport("COC00991", array(
            "P_NRORG" => 1,
            "P_CDFILIAL" => null,
            "P_ZEBRADO" => "S",
            "__format"  => "pdf"
        ));

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGettersAndSetters() {
        $this->assertEquals(BirtStrategy::BIRT, $this->birtStrategy->getName());
        $this->assertEquals("", $this->birtStrategy->getLogoPath());
        $this->birtStrategy->setLogoPath("logoPath.jpg");
        $this->assertEquals("logoPath.jpg", $this->birtStrategy->getLogoPath());
        $this->assertEquals("/report", $this->birtStrategy->getReportPath());
        $this->birtStrategy->setReportPath("/report/path");
        $this->assertEquals("/report/path", $this->birtStrategy->getReportPath());
        $this->assertEquals("", $this->birtStrategy->getConfPath());
        $this->birtStrategy->setConfPath("confPath");
        $this->assertEquals("confPath", $this->birtStrategy->getConfPath());
        $this->assertEquals(BirtStrategy::DYNAMIC_REL, $this->birtStrategy->getReportType());
        $this->birtStrategy->setReportType(BirtStrategy::STATIC_REL);
        $this->assertEquals(BirtStrategy::STATIC_REL, $this->birtStrategy->getReportType());
        $this->assertEquals("http://192.168.120.248:8080", $this->birtStrategy->getReportServerUrl());
        $this->birtStrategy->setReportServerUrl("http://localhost:8080");
        $this->assertEquals("http://localhost:8080", $this->birtStrategy->getReportServerUrl());
        $this->assertEquals(BirtStrategy::FORMAT_HTML, $this->birtStrategy->getReportFormat());
        $this->birtStrategy->setReportFormat(BirtStrategy::FORMAT_PDF);
        $this->assertEquals(BirtStrategy::FORMAT_PDF, $this->birtStrategy->getReportFormat());
        $this->assertEquals("run", $this->birtStrategy->getViewMode());
        $this->birtStrategy->setViewMode("newViewMode");
        $this->assertEquals("newViewMode", $this->birtStrategy->getViewMode());
        $this->assertEquals("pt_BR", $this->birtStrategy->getReportLocale());
        $this->birtStrategy->setReportLocale("en");
        $this->assertEquals("en", $this->birtStrategy->getReportLocale());
        $this->assertEquals("America/Sao_Paulo", $this->birtStrategy->getReportTimezone());
        $this->birtStrategy->setReportTimezone("America/New_York");
        $this->assertEquals("America/New_York", $this->birtStrategy->getReportTimezone());
    }
}
