import { NgModule, ModuleWithProviders } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule }   from '@angular/forms';

import { ApiService } from "../../service/api/api.service";
import { OrderService } from "../../service/order/order.service";
import { FormService } from "../../service/form/form.service";


import { LoaderComponent } from "../../components/shared/loader/loader.component";
import { AlertComponent } from "../../components/shared/alert/alert.component";
import { SweetAlertComponent } from "../../components/shared/sweet-alert/sweet-alert.component";
import { ContactsComponent } from "../../components/shared/contacts/contacts.component";
import { TablesComponent } from "../../components/shared/tables/tables.component";
import { FormsComponent } from "../../components/shared/forms/forms.component";

@NgModule({
    imports:      [ CommonModule, FormsModule ],
    declarations: [ LoaderComponent, AlertComponent, SweetAlertComponent, ContactsComponent, TablesComponent, FormsComponent],
    exports:      [ LoaderComponent, AlertComponent, SweetAlertComponent, ContactsComponent, TablesComponent, FormsComponent]
})
export class SharedModule {

    static forRoot(): ModuleWithProviders {
        return {
            ngModule: SharedModule,
            providers: [
                ApiService,
                OrderService,
                FormService
            ]
        };
    }
}
