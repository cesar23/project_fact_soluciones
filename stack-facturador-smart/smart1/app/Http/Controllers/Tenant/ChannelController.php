<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\ChannelCollection;
use App\Models\Tenant\Channel;
use App\Models\Tenant\ChannelDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return view('tenant.channels.index');
    }

    public function columns()
    {
        return [
            'channel_name' => 'Nombre',
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $channel = Channel::firstOrNew(['id' => $request->id]);

            $id = $request->id ?? null;
            $request->validate([
                'channel_name' => 'required|string|max:255|unique:tenant.channels_reg,channel_name,' . $id
            ]);

            $channel->fill($request->all());
            $channel->save();

            return response()->json([
                'success' => true,
                'message' => 'Canal actualizado exitosamente',
                'data' => $channel
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el canal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $channel = Channel::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $channel
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Canal no encontrado: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $channel = Channel::findOrFail($id);

            $request->validate([
                'channel_name' => 'required|string|max:255|unique:channels_reg,channel_name,' . $id
            ]);

            $channel->update([
                'channel_name' => $request->channel_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Canal actualizado exitosamente',
                'data' => $channel
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el canal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $channel = Channel::findOrFail($id);
             ChannelDocument::where('channel_reg_id', $id)->delete();
            $channel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Canal eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el canal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search channels by name.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function records(Request $request)
    {
        try {
            $query = $request->get('q', '');

            $channels = Channel::query();

            return new ChannelCollection($channels->paginate(config('tenant.items_per_page')));
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }
    public function allRecords()
    {
        $channels = Channel::all();
        return new ChannelCollection($channels);
    }

    public function record($id)
    {
        $channel = Channel::findOrFail($id);
        return $channel;
    }
}
