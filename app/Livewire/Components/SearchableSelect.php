<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\Modelable;

class SearchableSelect extends Component
{
    #[Modelable]
    public $value = '';
    
    public $search = '';
    public $options = [];
    public $placeholder = 'Sélectionner...';
    public $searchPlaceholder = 'Rechercher...';
    public $noResultsText = 'Aucun résultat';
    public $allowClear = true;
    public $disabled = false;
    public $name = '';
    
    // Props pour le style
    public $containerClass = '';
    public $inputClass = '';

    /**
     * Propriété calculée : Options filtrées selon la recherche
     */
    public function getFilteredOptionsProperty()
    {
        if (empty($this->search)) {
            return $this->options;
        }

        return array_filter($this->options, function($option) {
            $searchLower = mb_strtolower($this->search);
            $textLower = mb_strtolower($option['text'] ?? $option['label'] ?? '');
            return str_contains($textLower, $searchLower);
        });
    }

    /**
     * Sélectionner une option
     */
    public function selectOption($optionValue)
    {
        $oldValue = $this->value;
        $this->value = $optionValue;
        $this->search = '';
        
        // Émettre l'événement pour notifier le parent
        $this->dispatch('option-selected', value: $optionValue);
        
        // Si la valeur a changé, déclencher updatedValue pour que Livewire appelle updated* dans le parent
        if ($oldValue !== $optionValue) {
            $this->dispatch('$refresh');
        }
    }

    /**
     * Effacer la sélection
     */
    public function clear()
    {
        $oldValue = $this->value;
        $this->value = '';
        $this->search = '';
        $this->dispatch('option-cleared');
        
        // Si la valeur a changé, déclencher updatedValue
        if ($oldValue !== '') {
            $this->dispatch('$refresh');
        }
    }

    /**
     * Obtenir le texte de l'option sélectionnée
     */
    public function getSelectedTextProperty()
    {
        if (empty($this->value)) {
            return $this->placeholder;
        }

        $selected = collect($this->options)->firstWhere('value', $this->value);
        return $selected['text'] ?? $selected['label'] ?? $this->placeholder;
    }

    public function render()
    {
        return view('livewire.components.searchable-select');
    }
}
