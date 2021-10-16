<?php

namespace Service;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;

class Arquivo {
	
	protected $entityManager;

    public function __construct(\Doctrine\ORM\EntityManager $entityManager){
	   	$this->entityManager  = $entityManager;
	}
    
    public static function factoryWithEncryptedPassword(array $connectionParams, $salt, $config, $eventManager) {
        return DriverManager::getConnection($connectionParams, $config, $eventManager);
    }
    
    public function cadPatient($row) {
    	$this->entityManager->getConnection()->beginTransaction();
        try {
            $insertVerifiy = true;
            $params = [
                'IDPATIENT' => 'C'.$row['patientCPF'],
                'CPF' => isset($row['patientCPF']) ?  $row['patientCPF'] : null,
                'DATE' => isset($row['patientDate']) ?  $row['patientDate'] : null,
                'NAME' => isset($row['patientName']) ?  $row['patientName'] : null,
                'EMAIL' => isset($row['patientEmail']) ?  $row['patientEmail'] : null,
                'PHONE' => isset($row['patientPhone']) ?  $row['patientPhone'] : null,
                'ADDRESS' => isset($row['patientAddress']) ?  $row['patientAddress'] : null,
                'SELECTGENRE' => isset($row['patientSelected']) ?  $row['patientSelected'] : null
            ];
            foreach($params as $key => $line) {
                if ($insertVerifiy && $line == null) insertVerifiy = false;
            }
            if ($insertVerifiy) {
                $sql = "INSERT INTO PATIENT (IDPATIENT, NAME, EMAIL, PHONE, ADDRESS, DATE, CPF, SELECTGENRE) VALUES (:IDPATIENT, :NAME, :EMAIL, :PHONE, :ADDRESS, :DATE, :CPF, :SELECTGENRE)";
                $this->entityManager->executeQuery($sql, $params);
                $this->entityManager->getConnection()->commit();
                return 'Inserido';
            } else {
                return 'Favor não deixar nenhum campo sem preenchimento';
            }

        } catch (\Exception $error) {
            $this->entityManager->getConnection()->rollBack();
            return $error->getMessage();
        }
	}
	
    public function cadVaccine($row) {
    	$this->entityManager->getConnection()->beginTransaction();
        try {
            $insertVerifiy = true;
            $params = [
                'IDLOT' => 'L'.$row['lot'],
                'MANUFACTURER' => isset($row['manufacturer']) ?  $row['manufacturer'] : null,
                'LOT' => isset($row['lot']) ?  $row['lot'] : null,
                'DATE' => isset($row['expirationDate']) ?  $row['expirationDate'] : null,
                'NDOSES' => isset($row['nDoses']) ?  $row['nDoses'] : null,
                'INTERVAL' => isset($row['interval']) ?  $row['interval'] : null
            ];
            foreach($params as $key => $line) {
                if ($insertVerifiy && $line == null) insertVerifiy = false;
            }
            if ($insertVerifiy) {
                $sql = "INSERT INTO VACCINE (IDLOT, MANUFACTURER, LOT, DATE, NDOSES, INTERVAL) VALUES (:IDLOT, :MANUFACTURER, :LOT, :DATE, :NDOSES, :INTERVAL)";
                $this->entityManager->executeQuery($sql, $params);
                $this->entityManager->getConnection()->commit();
                return 'Inserido';
            } else {
                return 'Favor não deixar nenhum campo sem preenchimento';
            }
        } catch (\Exception $error) {
            $this->entityManager->getConnection()->rollBack();
            return $error->getMessage();
        }
	}
	
    public function cadVaccination($row) {
    	$this->entityManager->getConnection()->beginTransaction();
        try {
            $sqlInsert = "INSERT INTO VACCINATION (IDPATIENT, IDVACCINE, IDDOSE, DATE, NDOSE) VALUES (:IDPATIENT, :IDVACCINE, :IDDOSE, :DATE, :NDOSE)";
            $sqlGetHasVaccination = "SELECT * FROM VACCINATION WHERE IDPATIENT = :IDPATIENT AND IDDOSE = :IDDOSE AND IDVACCINE = :IDVACCINE";
            $sqlGetVaccineData = "SELECT * FROM VACCINE WHERE IDLOT = :IDDOSE";
            $insertVaccination = true;
            $insertVerifiy = true;
            $msg = '';
            $params = [
                'IDPATIENT' =>  'C'.$row['vaccinationPatientId'],
                'NDOSE' => isset($row['doseControl']) ?  $row['doseControl'] : null,
                'DATE' => isset($row['vaccinationDate']) ?  $row['vaccinationDate'] : null,
                'IDDOSE' => isset($row['vaccinationDosetId']) ?  $row['vaccinationDosetId'] : null,
                'IDVACCINE' => isset($row['vaccinationVaccineId']) ?  $row['vaccinationVaccineId'] : null
            ];
            foreach($params as $key => $line) {
                if ($insertVerifiy && $line == null) insertVerifiy = false;
            }
            if ($insertVerifiy) {
                $hasSomeVaccination = $this->entityManager->fetchAll($sqlGetHasVaccination, $params);
                if (count($hasSomeVaccination) > 0) {
                    $hasSomeVaccination = $hasSomeVaccination[0];
                    $paramsVaccine = [
                        'IDDOSE' => $hasSomeVaccination['IDDOSE'],
                    ];
                    $vaccineData = $this->entityManager->fetchAll($sqlGetVaccineData, $paramsVaccine);
                    $vaccineData = $vaccineData[0];
                    $dateIni = $hasSomeVaccination['DATE'];
                    $dateFin = $params['DATE'];
                    $diff = strtotime($dateFin) - strtotime($dateIni);
                    $days = floor($diff / (60 * 60 * 24));
                    
                    if ($insertVaccination && $days < $vaccineData['INTERVAL']) {
                        $insertVaccination = false;
                        $msg = 'Não é possível vacinar um paciente antes de um intervalo mínimo entre doses determinado.';
                    }
                    
                    if ($insertVaccination && $vaccineData['NDOSES'] <= count($hasSomeVaccination)) {
                        $insertVaccination = false;
                        $msg = 'Não é possível vacinar um paciente além do número de doses determinado.';
                    }
                    
                    $vaccineDataNew = $this->entityManager->fetchAll($sqlGetVaccineData, $params);
                    $vaccineDataNew = $vaccineDataNew[0];
                    
                    if ($insertVaccination && $vaccineDataNew['MANUFACTURER'] != $vaccineData['MANUFACTURER']) {
                        $insertVaccination = false;
                        $msg = 'Não é possível vacinar um paciente com doses de fabricantes diferentes.';
                    }
                }
                if ($insertVaccination) {
                    $this->entityManager->executeQuery($sqlInsert, $params);
                    $this->entityManager->getConnection()->commit();
                    $msg = 'Inserido';
                }
            } else {
                $msg = 'Favor não deixar nenhum campo sem preenchimento';
            }
            return $msg;
        } catch (\Exception $error) {
            $this->entityManager->getConnection()->rollBack();
            return $error->getMessage();
        }
    }	
    
}
