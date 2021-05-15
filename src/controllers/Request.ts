import Vue from 'vue'
import axios from "axios";

export default class Request extends Vue {
  body: any = {};
  route: string = "";
  callbackSuccess: Function = new Function();
  callbackError: Function = new Function();
  blockTouchEvents: boolean = false;
  showLoader: boolean = true;

  send(): any {
    //const urlBase: string = window.location.origin +'/backend/service/index.php';
    const urlBase: string ='http://joaogalvao1.zeedhi.com/workfolder/backend/service/index.php';
    const urlReq: string = urlBase + this.route;
    const self = this;
    axios.request({
      method : "post",
      url : urlReq
    }).then(function(response){
      const requestData: any = response.data.dataset;
      self.callbackSuccess(requestData);
    })
    .catch(function(error){
      self.callbackError(error);
    })
    .then(function(response){
        console.log(response); 
    });
  }

  /*.then(resposta => console.log(resposta.data))
      .catch(erro => console.error(erro));    
    axios.post(urlReq, {
      requestType: "Row",
      row: Object.assign(this.body)
    })
    */
}
