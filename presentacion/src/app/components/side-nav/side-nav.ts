import { Component, inject, signal } from '@angular/core';
import { MatListModule } from '@angular/material/list';
import { MatIconModule } from '@angular/material/icon';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../shared/services/auth-service';

type MenuItem = {
  icon: string;
  label: string;
  route: string;
  rol : number[]
};

@Component({
  selector: 'app-side-nav',
  imports: [MatIconModule, MatListModule, RouterModule],
  templateUrl: './side-nav.html',
  styleUrl: './side-nav.css'
})
export class SideNav {
  srvAuth = inject(AuthService);
  menuItem = signal<MenuItem[]>([
    {
      icon: 'home',
      label: 'Inicio',
      route: 'home',
      rol: [1, 2, 3, 4]
    },
    {
      icon: 'groups',
      label: 'Clientes',
      route: 'cliente',
      rol: [1, 2, 4]
    },
    {
      icon: 'tv',
      label: 'Artefactos',
      route: 'artefacto',
      rol: [1, 2]
    },
    {
      icon: 'engineering',
      label: 'Técnicos',
      route: 'tecnico',
      rol: [1, 2]
    },
    {
      icon: 'contacts',
      label: 'Oficinistas',
      route: 'oficinista',
      rol: [1]
    },
    {
      icon: 'handyman',
      label: 'Casos',
      route: 'casos',
      rol: [1, 2, 3, 4]
    },
    {
      icon: 'manage_accounts',
      label: 'Administradores',
      route: 'admin',
      rol: [1]
    }
  ]);
}