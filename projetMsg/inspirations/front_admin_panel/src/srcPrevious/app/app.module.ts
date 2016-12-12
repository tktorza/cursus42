import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { provideAuth } from "angular2-jwt";
import { HttpModule } from "@angular/http";
import { NgSemanticModule } from "ng-semantic";

import { AppComponent }  from './app.component';
import { routing } from "./routes";
import { HomeModule } from "./modules/home/home.module";
import { DashBoardModule } from "./modules/dashboard/dashboard.module";

import { HeaderComponent } from "./components/shared/header/header.component";
import { SideMenuComponent } from "./components/shared/sidemenu/sidemenu.component";

@NgModule({
    imports: [
        BrowserModule,
        HttpModule,
        NgSemanticModule,
        HomeModule,
        DashBoardModule,
        routing
    ],
    providers: [
        provideAuth({
            globalHeaders: [{"Content-type": "application/json"}],
            newJwtError: true,
            noTokenScheme: true
        })
    ],
    declarations: [ HeaderComponent, SideMenuComponent, AppComponent ],
    exports: [ HeaderComponent, SideMenuComponent ],
    bootstrap:    [ AppComponent ],
    schemas: [
        CUSTOM_ELEMENTS_SCHEMA
    ]
})
export class AppModule {}
