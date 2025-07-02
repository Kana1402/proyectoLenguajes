import { inject, Injectable } from '@angular/core';
import { IToken } from '../models/interface';
import { JwtHelperService } from '@auth0/angular-jwt';
import { environment } from '../../../environments/environment';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class TokenService {
  private readonly JWT_TOKEN = 'JWT_TOKEN';
  private readonly REFRESH_TOKEN = 'REFRESH_TOKEN';
  private http = inject(HttpClient);
  private refrescando = false;

  constructor() {}

  public setToken(token: string): void {
    localStorage.setItem(this.JWT_TOKEN, token);
  }

  public setRefreshToken(token: string): void {
    localStorage.setItem(this.REFRESH_TOKEN, token);
  }

  public setTokens(token: IToken): void {
    this.setToken(token.token);
    this.setRefreshToken(token.tkRef);
  }

  public get token(): any {
    return localStorage.getItem(this.JWT_TOKEN);
  }

  public get RefreshToken(): any{
    return localStorage.getItem(this.REFRESH_TOKEN);
  }

  public eliminarTokens(): void {
    localStorage.removeItem(this.JWT_TOKEN);
    localStorage.removeItem(this.REFRESH_TOKEN);
  }

  public decodeToken(): any {
    const helper = new JwtHelperService();
    return helper.decodeToken(this.token);
  }

  public jwtTokenExp(): boolean | Promise<boolean> {
    const helper = new JwtHelperService();
    return helper.isTokenExpired(this.token);
  }
  
  public tiempoExpToken() : number {
    return this.decodeToken().exp - (Date.now() / 1000)
  }

  public refreshTokens(){
    if(!this.refrescando){
      this.refrescando = true;
      return this.http.patch<IToken>(`${environment.servidor}/api/auth/refresh`,
        {
        idUsuario : (this.decodeToken().sub),
        tkRef : this.RefreshToken
        })
      .subscribe(
        tokens => {
          this.setTokens(tokens);
          this.refrescando = false;
        }
      )
    }
    return false;
  }

}
