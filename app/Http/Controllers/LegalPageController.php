<?php

namespace App\Http\Controllers;

class LegalPageController extends Controller
{
    public function privacy()
    {
        return view('legal.privacy', $this->sharedData('Kebijakan Privasi'));
    }

    public function terms()
    {
        return view('legal.terms', $this->sharedData('Syarat dan Ketentuan'));
    }

    public function cookies()
    {
        return view('legal.cookies', $this->sharedData('Kebijakan Cookie'));
    }

    private function sharedData(string $title): array
    {
        return [
            'legalTitle' => $title,
            'platformName' => config('seo.legal_name'),
            'contactEmail' => config('seo.contact_email'),
            'effectiveDate' => config('seo.legal_effective_date'),
        ];
    }
}
