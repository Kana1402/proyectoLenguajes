import { Component, inject } from '@angular/core';
import { MatIconButton } from '@angular/material/button';
import { MatDividerModule } from '@angular/material/divider';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { AuthService } from '../../shared/services/auth-service';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-header',
  imports: [MatIconModule, MatIconButton, MatMenuModule, MatDividerModule, RouterModule],
  templateUrl: './header.html',
  styleUrl: './header.css',
})
export class Header {
  srvAuth = inject(AuthService);


  loggOut() {
    this.srvAuth.loggOut();
  }

  loggIn() {}

  changePasswFrom() {}
}
