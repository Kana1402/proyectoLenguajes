import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { catchError, map, of } from 'rxjs';
import { environment } from '../../../environments/environment';

const _SERVER = environment.servidor;

@Injectable({
  providedIn: 'root'
})
export class UsuarioService {
  private readonly http = inject(HttpClient);

  constructor() { }

  resetearPassw(id: string) {
    return this.http.patch<any>(`${_SERVER}/api/usr/reset/${id}`, {})
      .pipe(
        map((error) => {
          catchError((error) =>{
            return of(error.status)
          })   
        })
      );
  }

}


