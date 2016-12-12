import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { HttpModule } from "@angular/http";
import { NgSemanticModule } from "ng-semantic";
import { CommonModule } from "@angular/common";
import { FormsModule } from '@angular/forms';

import { DashBoardComponent } from "./dashboard.component";
import { CartComponent } from "../../components/shared/cart/cart.component";
import { SharedModule } from "../shared/shared.module";
import { routing } from "./dashboard.routing";

@NgModule({
    imports: [
        CommonModule,
        HttpModule,
        FormsModule,
        routing,
        SharedModule.forRoot(),
        NgSemanticModule
    ],
    declarations: [
        DashBoardComponent,
        CartComponent
    ],
    bootstrap: [
        DashBoardComponent
    ],
    schemas: [
        CUSTOM_ELEMENTS_SCHEMA
    ]
})
export class DashBoardModule { }