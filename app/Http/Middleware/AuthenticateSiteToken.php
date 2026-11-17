<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Models\SiteUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSiteToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        abort_if($token === null, 401, __('index.unauthenticated'));

        $site = Site::query()
            ->where('active', true)
            ->where('code', (string) $request->header('X-Site'))
            ->first();

        abort_if($site === null, 422, __('index.site.unknown'));

        $driver = config("sites.drivers.{$site->driver}");

        abort_if($driver === null, 422, __('index.site.unsupported'));

        $client = app()->make($driver['client'], [
            'token' => $token,
            'url' => $site->url,
        ]);
        $user = $client->user($site);

        abort_if($user === null, 401, __('index.unauthenticated'));

        $siteUser = SiteUser::query()->createOrFirst(
            [
                'external_id' => (string) $user['id'],
                'site_id' => $site->id,
            ],
            [
                'name' => $user['name'],
                'role' => $user['role'],
            ],
        );

        $siteUser->fill([
            'name' => $user['name'],
            'role' => $user['role'],
        ]);
        $siteUser->save();

        $request->attributes->set('client', $client);
        $request->attributes->set('site', $site);
        $request->attributes->set('user', $siteUser);

        return $next($request);
    }
}
