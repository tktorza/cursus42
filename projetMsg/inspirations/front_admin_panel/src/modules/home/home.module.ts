import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { HttpModule } from "@angular/http";
import { NgSemanticModule } from "ng-semantic";
import { CommonModule } from "@angular/common";

import { HomeComponent } from "./home.component";
import { SharedModule } from "../shared/shared.module";
import { routing } from "./home.routing";

@NgModule({
    imports: [
        CommonModule,
        HttpModule,
        routing,
        SharedModule.forRoot(),
        NgSemanticModule
    ],
    declarations: [
        HomeComponent
    ],
    bootstrap: [
        HomeComponent
    ],
    schemas: [
        CUSTOM_ELEMENTS_SCHEMA
    ]
})
export class HomeModule { }