<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Promotion;
use App\Models\Tenant\Item;
use App\Models\Tenant\Catalogs\Tag;
use App\Models\Tenant\Company;
use App\Models\Tenant\ItemTag;
use App\Models\Tenant\ConfigurationEcommerce;
use Illuminate\Http\Request;

class ComercioController extends Controller
{
    public function records(Request $request)
    {
        // Obtener todas las promociones
        $promotions = Promotion::all();
        $input = $request->input('search');
        // Construir la URL completa para las imágenes de promociones
        foreach ($promotions as $promotion) {
            $promotion->image_url = url('storage/uploads/promotions/' . $promotion->image);
        }
        // Obtener la configuración de e-commerce
        $configuration = ConfigurationEcommerce::first();
        $company = Company::first();
        $favicon = $configuration->favicon;
        $trade_name = ($company) ? $company->trade_name : 'Ecommerce';
        if ($configuration->logo) {
            $logo_url = url('storage/uploads/logos/' . $configuration->logo);
        } else {
            $logo_url = asset('logo/tulogo.png');
        }
        $link_facebook = $configuration->link_facebook;
        $link_twitter = $configuration->link_twitter;
        $link_instagram = $configuration->link_youtube; // assuming youtube link is used for Instagram
        $contact_name = $configuration->information_contact_name;
        $contact_email = $configuration->information_contact_email;
        $contact_phone = $configuration->information_contact_phone;
        $contact_address = $configuration->information_contact_address;
        // Obtener los items que aplican a la tienda
        $items = Item::where('apply_store', 1)->where(function ($query) use ($input) {
            $query->where('description', 'LIKE', "%$input%")->orWhere('internal_id', 'LIKE', "%$input%");
        })->limit(50)->get();


        // Construir la URL completa para las imágenes de items
        foreach ($items as $item) {
            if ($item->image !== 'imagen-no-disponible.jpg') {
                $item->image_url = asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $item->image);
            } else {
                $item->image_url = asset("/logo/{$item->image}");
            }
            $item_unit_types = $item->item_unit_types;
            if ($item_unit_types) {
                $price_store = $item_unit_types->where('default_price_store', 1)->first();
                if ($price_store) {
                    $price_default = $price_store->price_default;
                    $prices = [$price_store->price1, $price_store->price2, $price_store->price3];
                    $item->sale_unit_price = $prices[$price_default - 1];
                }
            }
        }

        // Obtener todas las categorías
        $tags = Tag::all();

        // Enviar datos a la vista
        return view('comercio.index', compact('promotions', 'tags', 'logo_url', 'items', 'link_facebook', 'link_twitter', 'link_instagram', 'contact_name', 'contact_email', 'contact_phone', 'contact_address', 'favicon', 'trade_name'));
    }

    public function detalles($id)
    {
        $item = Item::findOrFail($id);
        $configuration = ConfigurationEcommerce::first();
        $logo_url = url('storage/uploads/logos/' . $configuration->logo);
        $phone_whatsapp = $configuration->phone_whatsapp;
        $link_facebook = $configuration->link_facebook;
        $link_twitter = $configuration->link_twitter;
        $link_instagram = $configuration->link_youtube; // assuming youtube link is used for Instagram
        $contact_name = $configuration->information_contact_name;
        $contact_email = $configuration->information_contact_email;
        $contact_phone = $configuration->information_contact_phone;
        $contact_address = $configuration->information_contact_address;

        // Construir la URL completa para la imagen del item
        if ($item->image !== 'imagen-no-disponible.jpg') {
            $item->image_url = asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $item->image);
        } else {
            $item->image_url = asset("/logo/{$item->image}");
        }
        $item_unit_types = $item->item_unit_types;
        if($item_unit_types){
            $price_store = $item_unit_types->where('default_price_store', 1)->first();
            if($price_store){
                $price_default = $price_store->price_default;
                $prices = [$price_store->price1, $price_store->price2, $price_store->price3];
                $item->sale_unit_price = $prices[$price_default - 1];
            }
        }
        // $item->image_url = url('storage/uploads/items/' . $item->image);
        // Obtener todas las categorías
        $tags = Tag::all();

        return view('comercio.detalles', compact('item', 'tags', 'logo_url', 'phone_whatsapp', 'link_facebook', 'link_twitter', 'link_instagram', 'contact_name', 'contact_email', 'contact_phone', 'contact_address'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        // Buscar en la tabla de items solo por la descripción
        $items = Item::where('description', 'LIKE', "%$query%")->get();

        foreach ($items as $item) {
            $item->image_url = url('storage/uploads/items/' . $item->image);
        }

        return response()->json($items);
    }

    public function filterByTag($tagId)
    {
        // Obtener el tag
        $tag = Tag::findOrFail($tagId);

        // Obtener los items asociados al tag
        $itemTags = ItemTag::where('tag_id', $tagId)->pluck('item_id');
        $items = Item::whereIn('id', $itemTags)->get();

        // Obtener la configuración de e-commerce
        $configuration = ConfigurationEcommerce::first();
        $logo_url = url('storage/uploads/logos/' . $configuration->logo);
        $link_facebook = $configuration->link_facebook;
        $link_twitter = $configuration->link_twitter;
        $link_instagram = $configuration->link_youtube; // assuming youtube link is used for Instagram
        $contact_name = $configuration->information_contact_name;
        $contact_email = $configuration->information_contact_email;
        $contact_phone = $configuration->information_contact_phone;
        $contact_address = $configuration->information_contact_address;

        // Obtener todas las categorías
        $tags = Tag::all();
        foreach ($items as $item) {
            // $item->image_url = url('storage/uploads/items/' . $item->image);
            if ($item->image !== 'imagen-no-disponible.jpg') {
                $item->image_url = asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $item->image);
            } else {
                $item->image_url = asset("/logo/{$item->image}");
            }
        }
        // Enviar datos a la vista
        return view('comercio.categorias', compact('tag', 'items', 'tags', 'logo_url', 'link_facebook', 'link_twitter', 'link_instagram', 'contact_name', 'contact_email', 'contact_phone', 'contact_address'));
    }
}
