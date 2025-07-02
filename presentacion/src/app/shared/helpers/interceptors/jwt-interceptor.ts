import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { TokenService } from '../../services/token-service';
import { AuthService } from '../../services/auth-service';

export const jwtInterceptor: HttpInterceptorFn = (req, next) => {
  const servToken = inject(TokenService);

  const token = servToken.token;
  if(inject(AuthService).isLoggedIn()){
    const cloneReq = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    return next(cloneReq);
  }
  return next(req);
};
