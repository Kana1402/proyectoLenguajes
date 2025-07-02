import { AfterViewInit, Component, inject, signal, ViewChild } from '@angular/core';
import { TipoCliente } from '../../shared/models/interface';
import { ClienteService } from '../../shared/services/cliente-service';

import { MatCardModule } from '@angular/material/card';
import { MatTableDataSource, MatTableModule } from '@angular/material/table';
import { MatIconModule } from '@angular/material/icon';
import { MatDialog } from '@angular/material/dialog';
import { FrmCliente } from '../forms/frm-cliente/frm-cliente';
import { DialogoGeneral } from '../forms/dialogo-general/dialogo-general';
import { UsuarioService } from '../../shared/services/usuario-service';
import { MatExpansionModule } from '@angular/material/expansion'
import { MatPaginator, MatPaginatorModule} from '@angular/material/paginator'
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { RouterModule } from '@angular/router';
import { PrintService } from '../../shared/services/print-service';


@Component({
  selector: 'app-cliente',
  imports: [MatCardModule, MatTableModule, MatIconModule, MatExpansionModule, MatPaginatorModule, MatFormFieldModule , MatInputModule, MatButtonModule, RouterModule],
  templateUrl: './cliente.html',
  styleUrl: './cliente.css'
})
export class Cliente implements AfterViewInit {
  private readonly clienteSrv = inject(ClienteService);
  private readonly usuarioSrv = inject(UsuarioService);
  private readonly dialogo = inject(MatDialog);
  private readonly printSrv = inject(PrintService);

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  panelOpenState = signal(false);
  columnas: string[] = ['idCliente', 'nombre', 'apellido1', 'apellido2', 'celular', 'correo', 'botonera'];
  //datos : any;
  dataSource = signal(new MatTableDataSource<TipoCliente>());
  filtro: any;

  mostrarDialogo(titulo: string, datos?: TipoCliente) {
    const dialogoRef = this.dialogo.open(FrmCliente,
      {
        width: '50vw',
        maxWidth: '35rem',
        data: {
          title: titulo,
          datos: datos
        },
        disableClose: true
      }
    );
    dialogoRef.afterClosed()
      .subscribe({
        next: (res) => {
          if (res != false) {
            this.resetearFiltro();
          }
        },

      })
  }

  resetearFiltro() {
    this.filtro = { idCliente: '', nombre: '', apellido1: '', apellido2: '' }
    this.filtrar();
  }

  filtrar() {
    this.clienteSrv.filtrar(this.filtro).subscribe({
      next: (data) => {
        console.log(data);
        this.dataSource.set(data);
      },
      error: (err) => console.error(err)
    });
  }

  limpiar(){
    this.resetearFiltro();
    (document.querySelector('#fidUsuario') as HTMLInputElement).value = '';
    (document.querySelector('#fnombre') as HTMLInputElement).value = '';
    (document.querySelector('#fapellido1') as HTMLInputElement).value = '';
    (document.querySelector('#fapellido2') as HTMLInputElement).value = '';
  }

  onNuevo() {
    this.mostrarDialogo('Nuevo Cliente');
  }

  onEditar(id: number) {
    this.clienteSrv.buscar(id)
      .subscribe({
        next: (data) => {
          this.mostrarDialogo('Editar Cliente', data);
        }
      })
  }
  onEliminar(id: number) {
    const dialogoRef = this.dialogo.open(DialogoGeneral, {
      data: {
        texto: `¿Está seguro de eliminar el cliente con id ${id}?`,
        icono: 'question_mark',
        textoAceptar: 'Sí',
        textoCancelar: 'No',
      }
    });
    dialogoRef.afterClosed().subscribe(result => {
      if (result === true) {
        this.clienteSrv.eliminar(id)
          .subscribe((res: any) => {
            this.dialogo.open(DialogoGeneral, {
              data: {
                texto: `Cliente con id ${id} eliminado correctamente`,
                icono: 'check',
                textoAceptar: 'Aceptar'
              },
            });
            this.resetearFiltro();
          })
      }
    });
  }
  onInfo(id: number) {

  }

  onFiltroChange(f : any){
    this.filtro = f;
    this.filtrar();
  }

  onResetearPassw(id: number){
    this.clienteSrv.buscar(id).subscribe({
      next: (data) => {
        const dialogoRef = this.dialogo.open(DialogoGeneral, {
      data: {
        texto: `¿Desea resetear la contraseña de ${data.nombre}?`,
        icono: 'question_mark',
        textoAceptar: 'Sí',
        textoCancelar: 'No',
      }
    });
    dialogoRef.afterClosed().subscribe(result => {
      if (result === true) {
        this.usuarioSrv.resetearPassw(data.idCliente).subscribe(() => {
            this.dialogo.open(DialogoGeneral, {
              data: {
                texto: `Contraseña restablecida`,
                icono: 'check',
                textoAceptar: 'Aceptar'
              }
            });
          });
        }
      });
      }
    });
  }

  onImprimir(){
    const encabezado = ['ID Cliente', 'Nombre', 'Teléfono', 'Celular', 'Correo'];
    this.clienteSrv.filtrar(this.filtro)
      .subscribe({
        next: (data) => {
          const cuerpo = Object(data).map((Obj: any) => {
            const datos = [
              Obj.idCliente,
              `${Obj.nombre} ${Obj.apellido1} ${Obj.apellido2}`,
              Obj.telefono,
              Obj.celular,
              Obj.correo,
            ];
            return datos;
          });
          this.printSrv.print(encabezado, cuerpo, 'Listado de CLientes', true)
        }
      });
  }

  ngAfterViewInit(): void {
    this.filtro = { idCliente: '', nombre: '', apellido1: '', apellido2: '' }
    this.filtrar();
  }
}