<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentoFactory> */
    use HasFactory;

    protected $table = 'documenti';

    // Mass assignment protection
    protected $fillable = [
        'pratica_id',
        'user_id',
        'categoria_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'descrizione',
        'hash_file'
    ];

    // RELAZIONI

    public function pratica()
    {
        return $this->belongsTo(Pratica::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaDocumento::class, 'categoria_id');
    }

    // SCOPE QUERY

    // Documenti PDF
    public function scopePdf($query)
    {
        return $query->where('mime_type', 'application/pdf');
    }

    // Documenti immagine
    public function scopeImmagini($query)
    {
        return $query->whereIn('mime_type', [
            'image/jpeg',
            'image/png',
            'image/gif'
        ]);
    }

    // METODI DI UTILITÀ

    // Dimensione formattata del file
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < 3) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    // Genera hash del file
    public function generateHash()
    {
        $this->hash_file = hash_file('sha256', storage_path('app/' . $this->file_path));
        $this->save();
    }

    // Verifica se il file è un duplicato
    public function isDuplicate()
    {
        if (!$this->hash_file) {
            $this->generateHash();
        }

        return static::where('hash_file', $this->hash_file)
            ->where('id', '!=', $this->id)
            ->exists();
    }

    // Boot del model per gestire gli eventi
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documento) {
            if (!empty($documento->file_path)) {
                try {
                    $disk = Storage::disk('pratiche');

                    // Se il file esiste nella directory temporanea, spostalo
                    if ($disk->exists($documento->file_path)) {
                        $originalName = basename($documento->file_path);

                        $documento->fill([
                            'original_name' => $originalName,
                            'user_id' => auth()->id(),
                            'mime_type' => $disk->mimeType($documento->file_path) ?? 'application/octet-stream',
                            'size' => $disk->size($documento->file_path) ?? 0,
                        ]);

                        // Calcola hash
                        $fullPath = $disk->path($documento->file_path);
                        if (file_exists($fullPath)) {
                            $documento->hash_file = hash_file('sha256', $fullPath);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Errore nel processare il documento: ' . $e->getMessage());

                    $documento->fill([
                        'mime_type' => 'application/octet-stream',
                        'size' => 0
                    ]);
                }
            }
        });
    }

    // Accessor per ottenere l'URL del file
    public function getFileUrlAttribute()
    {
        return Storage::disk('public')->url($this->file_path);
    }

    // Accessor per la dimensione formattata

}
