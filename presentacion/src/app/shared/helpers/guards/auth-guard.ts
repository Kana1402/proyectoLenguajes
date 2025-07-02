import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../../services/auth-service';
import { TokenService } from '../../services/token-service';
import { inject } from '@angular/core';

export const authGuard: CanActivateFn = (route, state) => {
  const srvAuth = inject(AuthService);
  const router = inject(Router);

  if(srvAuth.isLoggedIn()) {
    const rolUsuario = Number(srvAuth.userActualS().rol); 
    if(Object.keys(route.data).length !== 0 && route.data['roles'].indexOf(
      rolUsuario) === -1){
        return false;
      }
      return true;
  }
  srvAuth.loggOut();
  router.navigate(['/login']);
  return false;
};
