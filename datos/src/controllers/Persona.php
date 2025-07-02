<?php
    namespace App\controllers;

    use Psr\Container\ContainerInterface;

    use PDO;

    class Persona{
        protected $container;
        public function __construct(ContainerInterface $c){
            $this->container = $c;
        }



        public function createP($recurso, $rol, $datos){
            $sql= "SELECT nuevo$recurso(";
            foreach($datos as $key => $value){
                $sql .= ":$key,";
            }
            $sql= substr($sql, 0, -1) . ");";
            reset($datos);
            $claveId= key($datos);
                 
            $con=  $this->container->get('base_datos');
            $con->beginTransaction();
            $query = $con->prepare($sql);
            
            foreach($datos as $key => $value){
                $TIPO= gettype($value)=="integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
                $value=filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $query->bindValue($key, $value, $TIPO);
            };

            try{
                $query->execute();
                //$con->commit();
                $res= $query->fetch(PDO::FETCH_NUM)[0];
                $status= match($res) {
                    0 => 201,
                    1 => 409,
                };

                $id = $datos[$claveId];
            
                $sql = "SELECT nuevoUsuario(:idUsuario, :correo, :rol, :passw);";

                //Hash a la contraseña
                $passw= password_hash($id, PASSWORD_BCRYPT, ['cost' => 10]);
                

                $query = $con->prepare($sql);
                $query->bindValue(':idUsuario', $id, PDO::PARAM_STR);
                $query->bindValue(':correo', $datos['correo'], PDO::PARAM_STR);
                $query->bindValue(':rol', $rol, PDO::PARAM_INT);
                $query->bindValue(':passw', $passw, PDO::PARAM_STR);   

                $query->execute();

                if($status==409){
                    $con->rollBack();
                }else{
                    $con->commit();
                }
                $res= $query->fetch(PDO::FETCH_NUM)[0];
                
            } catch(\PDOException $e){
                $status= 500;
                $con->rollBack();
            }

            $query=null;
            $con=null;
          // return $response ->withStatus($status);
          return $status;
        }

         public function updateP($recurso, $datos, $id){
            $sql= "SELECT editar$recurso(:id,";

            foreach($datos as $key => $value){
                $sql .= ":$key,";
            }
            $sql= substr($sql, 0, -1) . ");";

            $con=  $this->container->get('base_datos');

            $con->beginTransaction();
            $query = $con->prepare($sql);

            $query->bindValue(':id', filter_var($id,FILTER_SANITIZE_SPECIAL_CHARS), PDO::PARAM_INT);

            foreach($datos as $key => $value){
                $TIPO= gettype($value)=="integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
                $value=filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $query->bindValue($key, $value, $TIPO);
            };

            $status= 200;
            try{
                $query->execute();
                $con->commit();
                $res= $query->fetch(PDO::FETCH_NUM)[0];
                $status= match($res) {
                    1 => 404,
                    0 => 200
                };

            } catch(\PDOException $e){
                $status= $e->getCode() == 23000 ? 409 : 500;
                $con->rollBack();
            }

            $query=null;
            $con=null;

            return $status;
        }

        public function deleteP($recurso, $id){

            $sql= "SELECT eliminar$recurso(:id);";
            $con=  $this->container->get('base_datos');

            $query = $con->prepare($sql);

            $query->bindValue('id', $id, PDO::PARAM_INT);
            $query->execute();
            $resp= $query->fetch(PDO::FETCH_NUM)[0];

           // $query->bindValue(':id', filter_var($id,FILTER_SANITIZE_SPECIAL_CHARS), PDO::PARAM_INT);
            $query=null;
            $con=null;

            return $resp > 0 ? 200 : 404;
        }

        public function filtrarP($recurso, $datos, $pag, $lim){

            // %idCliente%&%nombre%&%apellido1%&%apellido2%&
            $filtro= "%";
            foreach($datos as $key => $value){
                $filtro .= "$value%&%";
            }
            $filtro= substr($filtro, 0, -1);

            $sql="CALL filtrar$recurso('$filtro', $pag, $lim);";

            $con=  $this->container->get('base_datos');
            $query = $con->prepare($sql);

            $query->execute();
            $res= $query->fetchAll();
            $status= $query->rowCount()> 0 ? 200 : 204;

            $query=null;
            $con=null;

            return ["datos"=> $res, "status"=> $status];

        }

        public function buscarP($recurso, $id){

            $sql="CALL buscar$recurso($id, '');";

            $con=  $this->container->get('base_datos');
            $query = $con->prepare($sql);

            $query->execute();
            $res= $query->fetch(PDO::FETCH_ASSOC);
            $status= $query->rowCount()> 0 ? 200 : 204;
            $query=null;
            $con=null;
            return ["datos"=> $res, "status"=> $status];
        }
    }