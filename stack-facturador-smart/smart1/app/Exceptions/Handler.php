<?php

namespace App\Exceptions;

use Throwable;
use Http\Client\Exception\HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Arr;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            if ($this->isFrontend($request)) {
                return redirect()->guest('login');
            }
            return $this->errorResponse('No se encuentra autenticado', 401, $exception);
        }

        if ($exception instanceof TokenMismatchException) {
            if ($this->isFrontend($request)) {
                return redirect()->guest('login');
            }
            return $this->errorResponse('No se encuentra autenticado', 401, $exception);
        }
        if ($exception instanceof AuthorizationException) {
            return $this->errorResponse('No posee permisos para ejecutar esta acción', 403, $exception);
        }
        if ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }
        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('No se encontró la URL especificada', 404, $exception);
            //return response()->view('tenant.errors.404');
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('El método especificado en la petición no es válido', 405, $exception);
        }
        // if ($exception->getStatusCode() == 403 && !$this->isFrontend($request)) {
        //     return $this->errorResponse('Acceso denegado: Su cuenta está inactiva, comuníquese con el administrador', 403, $exception);
        // }
        if ($exception instanceof HttpException) {


            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode(), $exception);
        }
        if ($exception instanceof DuplicateDocumentException) {
            $response = [
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'success' => false,
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ];

            $additionalData = $exception->getAdditionalData();
            if (is_array($additionalData)) {
                $response = array_merge($response, $additionalData);
            }

            return response()->json($response, 400);
        }
        if (!$this->isFrontend($request)) {

            return $this->errorResponse('', 500, $exception);
        }
        $message = $exception->getMessage();
        $fromTemplate = str_contains($message, 'CoreFacturalo\Templates\pdf') && 
            !str_contains($message, 'CoreFacturalo\Templates\pdf\default\\');
        if ($fromTemplate) {
            $suggestions = [
                'Se ha detectado un error relacionado con una plantilla PDF.',
                'Por favor, cambie a la plantilla default desde:',
                '- Configuración > Plantillas PDF > PDF',
                '- Luego seleccione el establecimiento y elija la plantilla default.'
            ];
            $response = [
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'success' => false,
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'sugerencias' => $suggestions,
                'message' => implode(' ', $suggestions),
            ];
            return response()->json($response, 500);
        }

        return $this->errorResponse('', 500, $exception);
        return parent::render($request, $exception);
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

        if ($this->isFrontend($request)) {
            return $request->ajax() ? response()->json($errors, 422) : redirect()
                ->back()
                ->withInput($request->input())
                ->withErrors($errors);
        }

        return $this->errorResponse($errors, 422, $e);
    }

    private function isFrontend(Request $request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }

    private function errorResponse($message, $code, Throwable $exception)
    {
        // Validar que los parámetros no sean nulos
        $message = ($message === null || $message === '') ?
            ($exception->getMessage() ?? 'Error desconocido') : $message;

        $code = ($code === null || $code === '') ?
            ($exception->getCode() ?? 500) : $code;

        $file = $exception->getFile() ?? __FILE__;
        $line = $exception->getLine() ?? __LINE__;

        $short_file = basename($file);
        if (is_array($message)) {
            $all_messages = "";
            foreach ($message as $key => $value) {
                if (is_array($value)) {
                    $all_messages .= $key . " ";
                    foreach ($value as $key2 => $value2) {
                        $all_messages .= $value2 . " ";
                    }
                } else {
                    $all_messages .= $value . " ";
                }
            }
            $message = $all_messages;
        }
        return response()->json([
            'success' => false,
            'message' => $message . " " . $short_file . " " . $line,
            'file' => $short_file,
            'line' => $line
        ], $code);
    }
}
