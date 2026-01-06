<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Modules\LevelAccess\Traits\SystemActivityTrait;

    /**
     * Class RedirectModule
     *
     * @package App\Http\Middleware
     */
    class RedirectModule
    {

        use SystemActivityTrait;

        private $route_path;

        /**
         * Handle an incoming request.
         *
         * @param Request $request
         * @param Closure $next
         *
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            if ($request->user()->type == 'superadmin') {
                return $next($request);
            }
            if ($request->user()->type == 'client') {
                // return $next($request);
                return redirect()->route('tenant.ecommerce.index');
                
            }
            //obtiene el primer modulo del usuario (asume que es documents)
            //valida ciertas post que se necesitan usar asi no se pueda ingresar visualmente a "documents"
            $module = $request->user()->getModule();
            //obtener el path de la ruta actual
            $path = explode('/', $request->path());
            
            // 
            //todos los modulos del usuario
            $modules = $request->user()->getModules();
            // 
            //guardar el path de la ruta actual
            $this->route_path = $request->path();


            if (!$request->ajax()) {
                //si tiene modulos
                
                if (count($modules)) {
                    // if(count($modules) < 15){
                    //obtiene el grupo del path actual
                    $group = $this->getGroup($path, $module);
                    
                    //si encuentra el grupo
                    if ($group) {
                        //si no tiene permiso al grupo, si en los modulos no hay ni uno
                        //que tenga como valor el grupo actual
                        if ($this->getModuleByGroup($modules, $group) === 0) {
                            //redirecciona al modulo que tiene permiso
                            
                            return $this->redirectRoute($module);
                        }
                    }

                }
            }

            return $next($request);

        }

        /**
         * @param $path
         * @param $module
         *
         * @return string
         */
        private function getGroup($path, $module)
        {

            $firstLevel = $path[0] ?? null;
            $secondLevel = $path[1] ?? null;
            $group = null;
            ///* Module Documents */
            if (
                $firstLevel == "documents" ||
                $firstLevel == "dashboard" ||
                $firstLevel == "quotations" ||
                // $firstLevel == "production-orders" ||
                // $firstLevel == "dispatch_orders" ||
                // $firstLevel == "items" ||
                $firstLevel == "summaries" ||
                $firstLevel == "voided") {
                $group = "documents";
            } ///* Module purchases  */
            elseif (
                $firstLevel == "purchases" ||
                $firstLevel == "expenses") {
                $group = "purchases";
            } ///* Module advanced */
            elseif (
                $firstLevel == "retentions" ||
                $firstLevel == "dispatches" ||
                $firstLevel == "perceptions") {
                $group = "advanced";
            } ///* Module reports */
            elseif (
                $firstLevel == "list-reports" ||
                ($firstLevel == "reports" && $secondLevel == "purchases") ||
                ($firstLevel == "reports" && $secondLevel == "sales") ||
                ($firstLevel == "reports" && $secondLevel == "consistency-documents")) {
                $group = "reports";
            } // cuenta / listado de pagos
            elseif (
                $firstLevel == "cuenta") {
                $group = "cuenta";
            } ///* Module configuration */
            elseif (
                $firstLevel == "users" ||
                $firstLevel == "establishments") {
                $group = "establishments";
                // $group = "configuration";
            }//
            elseif (
                $firstLevel == "companies") {
                $group = "configuration";
                if (count($path) > 0 && $secondLevel == "uploads" && $module == "documents") {
                    $group = "documents";
                }
            }//
            elseif (
                $firstLevel == "catalogs" ||
                $firstLevel == "advanced") {
                $group = "configuration";
            } ///* Determinate type person */
            elseif (
                $firstLevel == "persons") {
                if ($secondLevel == "suppliers") {
                    $group = "purchases";
                }//
                elseif ($secondLevel == "customers") {
                    $group = "persons";
                } else {
                    $group = null;
                }
            }//
            elseif (
                $firstLevel == "person-types") {
                $group = "persons";
            } ///* Module pos */
            elseif (
                $firstLevel == "pos" ||
                $firstLevel == "cash") {
                $group = "pos";
            } ///* Module inventory */
            elseif (
                $firstLevel == "warehouses"||
                $firstLevel == "inventory" ||
                ($firstLevel == "reports" && $secondLevel == "kardex") ||
                ($firstLevel == "reports" && $secondLevel == "inventory")) {
                $group = "inventory";
            } ///* Module accounting */
            elseif (
                $firstLevel == "account") {
                $group = "accounting";
            } ///* Module finance */
            elseif (
                $firstLevel == "finances") {
                $group = "finance";
            }//
            elseif (
                $firstLevel == "orders" ||
                ($firstLevel == "ecommerce" && $secondLevel == "configuration") ||
                $firstLevel == "items_ecommerce" ||
                $firstLevel == "tags" ||
                $firstLevel == "promotions") {
                $group = "ecommerce";
            }//
            elseif (
                $firstLevel == "hotels" ||
                ($firstLevel == "hotels" && $secondLevel == "document-hotels")) {
                $group = "hotels";
            }//
            elseif (
                $firstLevel == "documentary-procedure") {
                $group = "documentary-procedure";
            }//
            elseif (
                $firstLevel == "digemid") {
                $group = "digemid";
            }//
            elseif (
                $firstLevel == "suscription") {
                $group = "suscription_app";
            }
            else if($this->existLevelInModules($firstLevel, ['items']))
            {
                $group = 'items';
            }

            return $group;
        }

        
        /**
         *
         * @param  string $level
         * @param  array $options
         * @return bool
         */
        private function existLevelInModules($level, $options)
        {
            return in_array($level, $options);
        }
        

        /**
         * @param $modules
         * @param $group
         *
         * @return mixed
         */
        private function getModuleByGroup($modules, $group)
        {

            $modules_x_group = $modules->filter(function ($module, $key) use ($group) {
                return $module->value === $group;
            });

            return $modules_x_group->count();
        }

        /**
         * @param $module
         *
         * @return RedirectResponse
         */
        private function redirectRoute($module)
        {
            // registrar log de actividades cuando el usuario no tiene permiso al modulo
            $this->saveGeneralSystemActivity(auth()->user(), 'module_access_error', $this->route_path);

            switch ($module) {

                case 'pos':
                    return redirect()->route('tenant.pos.index');

                case 'documents':
                    return redirect()->route('tenant.documents.create');

                case 'purchases':
                    return redirect()->route('tenant.purchases.index');

                case 'advanced':
                    return redirect()->route('tenant.dispatches.index');

                case 'reports':
                    return redirect()->route('tenant.reports.purchases.index');

                case 'configuration':
                    return redirect()->route('tenant.companies.create');

                case 'inventory':
                    return redirect()->route('inventory.index');
                    // return redirect()->route('warehouses.index');

                case 'accounting':
                    return redirect()->route('tenant.account.index');

                case 'finance':
                    return redirect()->route('tenant.finances.global_payments.index');

                case 'establishments':
                    return redirect()->route('tenant.users.index');

                case 'documentary-procedure':
                case 'hotels':
                    return redirect()->route('tenant.hotels.index');
                case 'digemid':
                    return redirect()->route('tenant.digemid.index');
                case 'suscription_app':
                    return redirect()->route('tenant.suscription.client.index');

                default;
                    return redirect()->route('tenant.dashboard.index');
                /*case 'ecommerce':
                    return redirect()->route('tenant.ecommerce.index');*/

            }
        }

    }
