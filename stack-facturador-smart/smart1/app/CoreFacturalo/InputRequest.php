<?php

namespace App\CoreFacturalo;

use Closure;

class InputRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  $type
     * @param  $service
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next, $type, $service)
    {
        $inputs = $request->all();
        if($service === 'api') {
            $inputs = $this->transformInputs($inputs, $type);
            if(key_exists('document_type_id', $inputs) && in_array($inputs['document_type_id'], ['09', '31'])) {
                $inputs = $this->registerInputs($inputs, $type);
            }
        }
        $inputs = $this->validationInputs($inputs, $type, $service);
        $request->replace($this->setInputs($inputs, $type, $service));
        return $next($request);
    } 

    private function transformInputs($inputs, $type)
    {
        $class = "App\\CoreFacturalo\\Requests\\Api\\Transform\\".ucfirst($type)."Transform";
        return $class::transform($inputs);
    }

    private function registerInputs($inputs, $type)
    {
        $class = "App\\CoreFacturalo\\Requests\\Api\\Register\\".ucfirst($type)."Register";
        return $class::register($inputs);
    }
    
    private function validationInputs($inputs, $type, $service)
    {
        $class = "App\\CoreFacturalo\\Requests\\".ucfirst($service)."\\Validation\\".ucfirst($type)."Validation";
        return $class::validation($inputs);
    }

    private function setInputs($inputs, $type, $service)
    {
        $class = "App\\CoreFacturalo\\Requests\\Inputs\\".ucfirst($type)."Input";
        return $class::set($inputs, $service);
    }
}
