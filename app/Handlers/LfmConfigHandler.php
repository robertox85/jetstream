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
        $nome = Pratica::findOrFail($praticaId)->numero_pratica;
        // if null return 0

        if (!$nome) {
            return 0;
        }

        // get only numbers with separator (2024-000 - 2024-001)
        $nome = preg_replace('/[^0-9-]/', '', $nome);
        // remove first -- if exists
        $nome = ltrim($nome, '-');

        return $nome;


    }
}
