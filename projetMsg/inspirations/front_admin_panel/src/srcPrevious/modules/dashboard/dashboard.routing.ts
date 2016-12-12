import { Routes, RouterModule } from '@angular/router';

import { DashBoardComponent } from './dashboard.component';

export const routes: Routes = [
    { path: 'dashboard', component: DashBoardComponent }
];

export const routing = RouterModule.forChild(routes);