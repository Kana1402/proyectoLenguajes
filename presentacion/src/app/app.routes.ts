import { Routes } from '@angular/router';
import { Home } from './components/home/home';
import { Cliente } from './components/cliente/cliente';
import { Page404 } from './components/page404/page404';
import { Login } from './components/login/login';
import { authGuard } from './shared/helpers/guards/auth-guard';
import { Role } from './shared/models/role';
import { loginGuard } from './shared/helpers/guards/login-guard';

export const routes: Routes = [
    {path: '', redirectTo: 'home', pathMatch: 'full' },
    {path: 'home', component: Home },
    {path: 'cliente', component: Cliente,
        canActivate : [authGuard],
        data : {
            roles: [Role.Admin,Role.Oficinista,Role.Cliente]
        }
    },
    {path: 'login', component: Login, canActivate: [loginGuard]},
    
    {path: '**', component: Page404}

];
