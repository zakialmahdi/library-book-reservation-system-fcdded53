<?php

namespace App\Livewire\Admin;

use App\Actions\Genres\CreateGenreAction;
use App\Actions\Genres\DeleteGenreAction;
use App\Actions\Genres\UpdateGenreAction;
use App\Http\Requests\Genres\GenreRequest;
use App\Models\Genre;
use Livewire\Component;

class GenreManager extends Component
{
    public string $name = '';
    public ?int $editingGenreId = null;
    public string $search = '';

    public function editGenre(int $genreId): void
    {
        $genre = Genre::findOrFail($genreId);
        $this->authorize('update', $genre);

        $this->editingGenreId = $genre->genre_id;
        $this->name = $genre->name;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'editingGenreId']);
        $this->resetValidation();
    }

    public function saveGenre(CreateGenreAction $createAction, UpdateGenreAction $updateAction): void
    {
        $validated = GenreRequest::validatePayload(['name' => $this->name], $this->editingGenreId);

        if ($this->editingGenreId) {
            $genre = Genre::findOrFail($this->editingGenreId);
            $this->authorize('update', $genre);
            $updateAction->execute($genre, $validated);
            session()->flash('status', "Updated genre \"{$genre->name}\".");
        } else {
            $this->authorize('create', Genre::class);
            $genre = $createAction->execute($validated);
            session()->flash('status', "Created new genre \"{$genre->name}\".");
        }

        $this->reset(['name', 'editingGenreId']);
    }

    public function deleteGenre(int $genreId, DeleteGenreAction $action): void
    {
        $genre = Genre::findOrFail($genreId);
        $this->authorize('delete', $genre);

        $name = $genre->name;
        $action->execute($genre);
        session()->flash('status', "Deleted genre \"{$name}\".");
    }

    public function render()
    {
        $cleanSearch = strip_tags(trim($this->search));

        $genres = Genre::with(['books' => fn ($q) => $q->orderBy('title')])
            ->withCount('books')
            ->when($cleanSearch !== '', fn ($q) => $q->where('name', 'like', "%{$cleanSearch}%"))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.genre-manager', [
            'genres' => $genres,
        ]);
    }
}
