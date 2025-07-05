@component('mail::message')
Bonjour {{ $data['nom'] }},

Votre forfait pour le compte **{{ $data['type_compte'] }}** expire le {{ \Carbon\Carbon::parse($data['dateend'])->format('d/m/Y') }}.

Nous vous recommandons de renouveler votre abonnement pour continuer à bénéficier de nos services.

@component('mail::button', ['url' => config('app.url').'/renewal'])
Renouveler maintenant
@endcomponent

Cordialement,l'équipe<br>
{{ config('app.name') }}
@endcomponent