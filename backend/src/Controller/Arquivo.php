<?php
namespace Controller;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DataSource\DataSet;

class Arquivo {

	protected $arquivoService;

	public function __construct(\Service\Arquivo $arquivoService) {
		$this->arquivoService =  $arquivoService;
    }

	public function cadPatient(Request\Row $request, Response $response) {
		$row = $request->getRow();
		$resp = $this->arquivoService->cadPatient($row);
		$response->addDataSet(new DataSet('cadPatient', [$resp]));  
	}

	public function cadVaccination(Request\Row $request, Response $response) {
		$row = $request->getRow();
		$resp = $this->arquivoService->cadVaccination($row);
		$response->addDataSet(new DataSet('cadVaccination', [$resp]));  
	}

	public function cadVaccine(Request\Row $request, Response $response) {
		$row = $request->getRow();
		$resp = $this->arquivoService->cadVaccine($row);
		$response->addDataSet(new DataSet('cadVaccine', [$resp]));  
	}
	
}