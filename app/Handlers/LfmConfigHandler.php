<?php

namespace App\Handlers;

use App\Models\Pratica;
use Illuminate\Support\Facades\Log;
use UniSharp\LaravelFilemanager\Handlers\ConfigHandler;

class LfmConfigHandler extends ConfigHandler
{

    public function userField(): int|string|null
    {

       // return auth()->id();

        $praticaId = request()->route('pratica');

        if (!$praticaId) {
            abort(403, 'ID pratica mancante.');
        }
        // return pratica name formatted as folder name
        $nome = Pratica::findOrFail($praticaId)->nome;
        $nome = str_replace(' ', '_', $nome);
        $nome = str_replace('/', '_', $nome);
        return $nome;


    }
}
