import Vue from 'vue'
import Request from './Request'
//import Modal from '../components/Modal.vue';

export default class Home extends Vue {
  
  chooseScreen: string = '';
  options: any = [{ name: 'Masculino', key: 'male' },{ name: 'Feminino', key: 'female' },{ name: 'LGBTQI+', key: 'lgbt' },{ name: 'Prefere não dizer', key: 'unspoken' }];
  //Cadastro de vacinas
  manufacturer: string = '';
  lot: string = '';
  expirationDate: string = '';
  nDoses: string = '';
  interval: string = '';
  //Cadastro de Paciente
  patientName: string = '';
  patientEmail: string = '';
  patientPhone: string = '';
  patientAddress: string = '';
  patientDate: string = '';
  patientCPF: string = '';
  patientSelected: string = '';
  //Resgistro de Vacinação
  vaccinationDate: string = '';
  vaccinationPatientId: string = '';
  vaccinationVaccineId: string = '';
  vaccinationDosetId: string = '';
  doseControl: string = '';
  //Mensagem tela ResponseUser
  modalMessage: string = '';
  //Escolha de Div para tela
  changeScreenHome() {
    this.chooseScreen = '';
    this.$forceUpdate();
    this.clearData();
  }
  changeScreenCadPatient() {
    this.chooseScreen = 'cadPatient';
    this.$forceUpdate();
    this.clearData();
  }
  changeScreenCadVaccination() {
    this.chooseScreen = 'cadVaccination';
    this.$forceUpdate();
    this.clearData();
  }  
  changeScreenCadVaccine() {
    this.chooseScreen = 'cadVaccine';
    this.$forceUpdate();
    this.clearData();
  }
  changeScreenResponseUser() {
    this.chooseScreen = 'responseUser';
    this.$forceUpdate();
    this.clearData();
  }
  //Data Manipulation
  clearData () {
    this.manufacturer = '';
    this.lot = '';
    this.expirationDate = '';
    this.nDoses = '';
    this.interval = '';
    this.patientName = '';
    this.patientEmail = '';
    this.patientPhone = '';
    this.patientAddress = '';
    this.patientDate = '';
    this.patientCPF = '';
    this.patientSelected = '';
    this.vaccinationDate = '';
    this.vaccinationPatientId = '';
    this.vaccinationVaccineId = '';
    this.vaccinationDosetId = '';
    this.doseControl = '';
  }
  goCadVaccine() {
    const req  = new Request();
    req.route  = '/cadVaccine';
    req.body   = {
      'manufacturer': this.manufacturer,
      'lot': this.lot,
      'expirationDate': this.expirationDate,
      'nDoses': this.nDoses,
      'interval': this.interval
    };
    req.callbackSuccess = (response: string) => {
      this.clearData();
      this.modalMessage = response[0] ? response[0] : response;
      this.changeScreenResponseUser();
    };
    req.callbackError = (error: any) => {
        this.modalMessage = error[0] ? error[0] : error;
        this.changeScreenResponseUser();
    }
    req.send();
  }
  goCadVaccination() {
    const req  = new Request();
    req.route  = '/cadVaccination';
    req.body   = {
      'vaccinationDate': this.vaccinationDate,
      'vaccinationPatientId': this.vaccinationPatientId,
      'vaccinationVaccineId': this.vaccinationVaccineId,
      'vaccinationDosetId': this.vaccinationDosetId,
      'doseControl': this.doseControl
    };
    req.callbackSuccess = (response: string) => {
      this.clearData();
      this.modalMessage = response[0] ? response[0] : response;
      this.changeScreenResponseUser();
    };
    req.callbackError = (error: any) => {
        this.modalMessage = error[0] ? error[0] : error;
        this.changeScreenResponseUser();
    }
    req.send();
  }
  goCadPatient() {  
    const req  = new Request();
    req.route  = '/cadPatient';
    req.body   = {
      'patientName': this.patientName,
      'patientEmail': this.patientEmail,
      'patientPhone': this.patientPhone,
      'patientAddress': this.patientAddress,
      'patientDate': this.patientDate,
      'patientCPF': this.patientCPF,
      'patientSelected': this.patientSelected  
    };
    req.callbackSuccess = (response: string) => {
      this.clearData();
      this.modalMessage = response[0] ? response[0] : response;
      this.changeScreenResponseUser();
    };
    req.callbackError = (error: any) => {
        console.log(error)
        this.modalMessage = error[0] ? error[0] : error;
        this.changeScreenResponseUser();
    }
    req.send();
  }


}