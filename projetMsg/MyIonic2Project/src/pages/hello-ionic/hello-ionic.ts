import { Component } from '@angular/core';


@Component({
  selector: 'page-hello-ionic',
  templateUrl: 'hello-ionic.html'
})
export class HelloIonicPage {
    private msg: string;
    private from: any;
    private messages: any;
    private pseudo: string;
    private myMessages: any;

  constructor() {
    this.pseudo = "mcheun";
    this.from = ['Hello lovely', 'I miss you', 'Can you go back home', '?'];
    this.myMessages = [];
    this.messages = [];
    for (var i = 0; i < this.from.length; i++){
      this.messages.push({from: 'her', msg: this.from[i]});
    }

  }
  send(){
    //envoyer msg cripté déjà
    console.log(this.messages)
    if (this.msg){
      this.messages.push({from: 'me', msg: this.msg});
    console.log(this.msg);
    }else{
      this.messages.push({from: 'her', msg: 'hello'});
    }
  }
}
