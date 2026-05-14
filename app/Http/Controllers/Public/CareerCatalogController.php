<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Services\StudentService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CareerCatalogController extends Controller
{
    /** Carreras públicas destacadas (códigos) y resto del catálogo. */
    private const array FEATURED_CODES = [
        'MED',
        'ICV',
        'ISI',
        'ICI',
        'ARC',
        'MVE',
        'DER',
        'ENF',
        'PSI',
        'IAG',
    ];

    /** Listado público de carreras activas. */
    public function __invoke(StudentService $studentService): View
    {
        $careers = $studentService->cachedActiveCareers();

        $featured = $this->orderedFeatured($careers, self::FEATURED_CODES);
        $other = $careers
            ->whereNotIn('code', self::FEATURED_CODES)
            ->sortBy('name')
            ->values();

        return view('public.careers', [
            'featuredCareers' => $featured,
            'otherCareers' => $other,
        ]);
    }

    /**
     * @param  Collection<int, Career>  $careers
     * @param  list<string>  $order
     * @return Collection<int, Career>
     */
    private function orderedFeatured(Collection $careers, array $order): Collection
    {
        $out = collect();
        foreach ($order as $code) {
            $c = $careers->firstWhere('code', $code);
            if ($c !== null) {
                $out->push($c);
            }
        }

        return $out;
    }
}
