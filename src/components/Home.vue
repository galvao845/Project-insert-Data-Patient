<template>
    <!-- Tela Inicial -->
    <div v-if="chooseScreen == ''">
      <h1>Bem Vindo ao nosso controle de vacinas</h1>
      <p class="firstLabel">Selecione abaixo a opção desejada:</p>  
      <div class="">
        <div>
          <button class="buttonHome" block variant="primary" v-on:click="changeScreenCadVaccine()">Cadastro de Vacinas</button>
        </div>
        <div>
          <button class="buttonHome" block variant="primary" v-on:click="changeScreenCadPatient()">Cadastro de Pacientes</button>
        </div>
        <div>
          <button class="buttonHome" block variant="primary" v-on:click="changeScreenCadVaccination()">Registro de vacinação</button>
        </div>
      </div>
      <p class="lastLabel">Feito por: João Galvão</p>
    </div>
    <!-- Cadastro de vacinas -->
    <div v-else-if="chooseScreen == 'cadVaccine'">
      <h1>Cadastro de Vacinas</h1>
      <form class ="formClass">
        <div class="group">
          <label for="user" class="label">Fabricante</label>
          <input id="manufacturer" class="inputData" v-model="manufacturer" type="text" name="manufacturer">
        </div>
        <div class="group">
          <label for="user" class="label">Lote</label>
          <input id="lot" class="inputData" v-model="lot" type="text" name="lot">
        </div>
        <div class="group">
          <label for="user" class="label">Data de Validade</label>
          <input id="expirationDate" class="inputData" v-model="expirationDate" type="text" name="expirationDate">
        </div>
        <div class="group">
          <label for="user" class="label">Número de doses</label>
          <input id="nDoses" class="inputData" v-model="nDoses" type="text" name="nDoses">
        </div>
        <div class="group">
          <label for="user" class="label">Intervalo mínimo entre doses</label>
          <input id="interval" class="inputData" v-model="interval" type="text" name="interval">
        </div>
        <div class="divForm">
          <input type="submit" class="inputGoBack-Send" value="Cancelar" v-on:click="changeScreenHome()">
          <input type="submit" class="inputGoBack-Send" value="Salvar" v-on:click="goCadVaccine()">
        </div>
      </form> 
    </div>
    <!-- Cadastro de Pacientes -->
    <div v-else-if="chooseScreen == 'cadPatient'">
      <h1>Cadastro de Pacientes</h1>
      <form class ="formClass">
        <div class="group">
          <label for="user" class="label">Nome</label>
          <input id="patientName" class="inputData" v-model="patientName" type="text" name="pacientName" >
        </div>
        <div class="group">
          <label for="pass" class="label">E-mail</label>
          <input id="patientEmail" class="inputData" v-model="patientEmail" type="email" name="patientEmail">
        </div>
        <div class="group">
          <label for="pass" class="label">Telefone</label>
          <input id="patientPhone" class="inputData" v-model="patientPhone" type="text" name="patientPhone">
        </div>
        <div class="group">
          <label for="pass" class="label">Endereço Completo</label>
          <input id="patientAddress" class="inputData" v-model="patientAddress" type="text" name="patientAddress">
        </div>    
        <div class="group">
          <label for="pass" class="label">Intervalo mínimo entre doses</label>
          <input id="patientDate" class="inputData" v-model="patientDate" type="text" name="patientDate">
       </div>        
        <div class="group">
          <label for="pass" class="label">CPF</label>
          <input id="patientCPF" class="inputData" v-model="patientCPF" type="text" name="patientCPF">
       </div>        
        <div class="group">
          <label for="pass" class="label">Gênero</label>
          <select v-model="patientSelected" class="inputDataSelect">
            <option v-for="option in options" v-bind:value="option.name" v-bind:key="option.key">
              {{ option.name }}
            </option>
          </select>
       </div>    
        <div class="divForm">
          <input type="submit" class="inputGoBack-Send" value="Cancelar" v-on:click="changeScreenHome()">
          <input type="submit" class="inputGoBack-Send" value="Salvar" v-on:click="goCadPatient()">
        </div>
      </form> 
    </div>
    <!-- Registro vacinação -->
    <div v-else-if="chooseScreen == 'cadVaccination'">
      <h1>Registro vacinação</h1>
      <form class ="formClass">
        <div class="group">
          <label for="user" class="label">Identificação do paciente</label>
          <input id="vaccinationPatientId" class="inputData" v-model="vaccinationPatientId" type="text" name="vaccinationPatientId">
        </div>
        <div class="group">
          <label for="pass" class="label">Identificação da vacina</label>
          <input id="vaccinationVaccineId" class="inputData" v-model="vaccinationVaccineId" type="text" name="vaccinationVaccineId">
        </div>
        <div class="group">
          <label for="pass" class="label">Identificação da dose</label>
          <input id="vaccinationDosetId" class="inputData" v-model="vaccinationDosetId" type="text" name="vaccinationDosetId">
        </div>
        <div class="group">
          <label for="pass" class="label">Data de vacinação</label>
          <input id="vaccinationDate" class="inputData" v-model="vaccinationDate" type="date" name="vaccinationDate">
        </div>    
        <div class="group">
          <label for="pass" class="label">Controle de dose</label>
            <input id="doseControl" class="inputData" v-model="doseControl" type="text" name="doseControl">
       </div>        
        <div class="divForm">
          <input type="submit" class="inputGoBack-Send" value="Cancelar" v-on:click="changeScreenHome()">
          <input type="submit" class="inputGoBack-Send" value="Salvar" v-on:click="goCadVaccination()">
        </div>
      </form> 
    </div>
    <!-- Registro vacinação -->
    <div v-else-if="chooseScreen == 'responseUser'">
      <h1>Requisição Feita</h1>
      <div class="group">
        <label for="pass" class="label">{{ modalMessage }}</label>
      </div>        
      <div class="divForm">
        <input type="submit" class="inputButtonResponse" value="Confirmar" v-on:click="changeScreenHome()">
      </div>   
    </div>
</template>

<script lang="ts" src='../controllers/Home.ts'></script>

<style scoped>
  .group{
    margin-bottom:15px;
  }
  .group .label{
    font-size:12px;
    text-transform:uppercase;
    display:block;
    font-size:12px;  
    align-content: center;
    align-items: center;  
    text-align: center;
    cursor: pointer;
  }
  h3 {
    margin: 40px 0 0;
  }
  .buttonHome {
    background-color: rgb(228, 255, 255);
    margin-top: 10px;
    height: 30%;
    width: 50%;
    align-content: center;
    align-items: center;
    border: 4px;
    border-color: rgb(133, 184, 167);
    padding: 60px;
    text-align: center;
    text-decoration: none;  
    margin: 4px 2px;
    transition-duration: 0.4s;
    cursor: pointer;
    font-size: 25px;
  }
  .firstLabel {
    margin-top: 5%;
    margin-bottom: 2%;
  }
  .lastLabel {
    margin-top: 14%;
    color:rgb(228, 255, 255);
  
  }
  .divForm {
    padding-top: 1%;
  }
  .inputGoBack-Send {
    background-color: rgb(228, 255, 255);
    margin-top: 10px;
    height: 4%;
    width: 12.5%;
    align-content: center;
    align-items: center;
    border: 4px;
    border-color: rgb(133, 184, 167);
    text-align: center;
    text-decoration: none;  
    margin: 4px 2px;
    transition-duration: 0.4s;
    cursor: pointer;
    font-size: 17px;
  }
  .formClass {
    padding-top: 4%;
  }
  .inputData {
    width: 25%;
  }
  .inputDataSelect {
    width: 25.5%;
  }
  .inputButtonResponse {
    background-color: rgb(228, 255, 255);
    margin-top: 10px;
    height: 4%;
    width: 25%;
    align-content: center;
    align-items: center;
    border: 4px;
    border-color: rgb(133, 184, 167);
    text-align: center;
    text-decoration: none;  
    margin: 4px 2px;
    transition-duration: 0.4s;
    cursor: pointer;
    font-size: 17px;
  }
</style>