import { Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatDialogModule, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-dialogo-general',
  imports: [MatDialogModule, MatButtonModule, MatIconModule],
  templateUrl: './dialogo-general.html',
  styleUrl: './dialogo-general.css'
})
export class DialogoGeneral {
  readonly data = inject(MAT_DIALOG_DATA);
}
