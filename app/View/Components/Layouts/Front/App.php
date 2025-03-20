<?php

namespace App\View\Components\Layouts\Front;

use Illuminate\View\Component;

class App extends Component
{
    public $title = ''; public $description = '';
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $scripts;
    public function __construct()
    {

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $indexFollow = 'index, follow';
        return view('components.layouts.front.app', compact('indexFollow'));
    }
}
