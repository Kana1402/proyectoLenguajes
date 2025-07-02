import { Component, inject, signal } from '@angular/core';
import { MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { ReactiveFormsModule, FormBuilder, FormGroup} from '@angular/forms';
import { MatInputModule } from '@angular/material/input';
import { MatDividerModule } from '@angular/material/divider';
import { AuthService } from '../../../shared/services/auth-service';

@Component({
  selector: 'app-frm-login',
  imports: [MatDialogModule, MatIconModule, MatButtonModule, MatFormFieldModule, ReactiveFormsModule, MatInputModule, MatDividerModule],
  templateUrl: './frm-login.html',
  styleUrl: './frm-login.css'
})
export class FrmLogin {
  readonly dialogRef = inject(MatDialogRef<FrmLogin>);
  frmLogin: FormGroup;
  private builder = inject(FormBuilder);
  private srvAuth = inject(AuthService);
  public errorLogin = signal(false);

  constructor() {
    this.frmLogin = this.builder.group({
      id: (0),
      idUsuario: (''),
      passw: (''),
    });
  }

  onLogin() {
    this.srvAuth.login(this.frmLogin.value)
      .subscribe((res) => {
        this.errorLogin.set(!res || res === 401);
        if(!this.errorLogin()){
          this.dialogRef.close();
        }
      })
  }
}
