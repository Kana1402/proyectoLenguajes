import { inject, Injectable } from '@angular/core';
import { HttpClient,HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { TipoCliente } from '../models/interface';
import { retry, map, catchError } from 'rxjs';
import { environment } from '../../../environments/environment';

const _SERVER = environment.servidor;

@Injectable({
  providedIn: 'root'
})
export class ClienteService {
  private readonly http = inject(HttpClient);
  constructor() { }

  filtrar(parametros : any) {
    let paranms = new HttpParams;
    for (const prop in parametros) {
        paranms = paranms.append(prop, parametros[prop])
    }
    return this.http.get<any>(`${_SERVER}/api/cliente/filtrar/0/5`, 
        { params : paranms }
      );  
  }

  guardar(datos: TipoCliente, id?: number) :Observable<TipoCliente> {
    delete datos.id;// Eliminar el id si existe, ya que no se debe enviar al servidor al crear un nuevo cliente
    console.log(datos);
    if(id){
      return this.http.put<any>(`${_SERVER}/api/cliente/${id}`, datos);
    }
    return this.http.post<any>(`${_SERVER}/api/cliente`, datos);
  }

  eliminar(id: number) : Observable<any> {
    return this.http.delete<any>(`${_SERVER}/api/cliente/${id}`)
    .pipe(
      retry(1),
      map(()=> true),
      catchError(this.handleError),
    );
  }
  buscar(id: number){
    return this.http.get<TipoCliente>(`${_SERVER}/api/cliente/${id}`)
  }
  private handleError(error: any) {
    return throwError(
      () => {
        return error.status
      }
    )
  }
}
