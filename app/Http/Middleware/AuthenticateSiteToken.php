<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Models\SiteUser;
use App\Support\Jwt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateSiteToken
{
    /**
     * @return array<string, mixed>|null
     */
    private function claims(string $token): ?array
    {
        try {
            return Jwt::decode($token);
        } catch (Throwable) {
            return null;
        }
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        abort_if($token === null, 401, __('index.unauthenticated'));

        $claims = $this->claims($token);

        abort_if($claims === null, 401, __('index.unauthenticated'));

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

        $roles = implode(', ', $claims['roles']);

        $siteUser = SiteUser::query()->createOrFirst(
            [
                'external_id' => (string) $claims['sub'],
                'site_id' => $site->id,
            ],
            [
                'name' => $claims['name'],
                'role' => $roles ?: null,
            ],
        );

        $siteUser->fill([
            'name' => $claims['name'],
            'role' => $roles ?: null,
        ]);
        $siteUser->save();

        $request->attributes->set('client', $client);
        $request->attributes->set('permissions', $claims['permissions']);
        $request->attributes->set('site', $site);
        $request->attributes->set('user', $siteUser);

        return $next($request);
    }
}
