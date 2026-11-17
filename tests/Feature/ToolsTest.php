<?php

namespace Tests\Feature;

use App\Ai\Tools\Wms\Read\GetHandover;
use App\Ai\Tools\Wms\Read\GetHandovers;
use App\Ai\Tools\Wms\Write\CancelHandover;
use App\Ai\Tools\Wms\Write\CreateHandover;
use App\Services\WmsClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class ToolsTest extends TestCase
{
    private function client(): WmsClient
    {
        return new WmsClient('http://wms.test/api', 'token');
    }

    public function test_action_tool_posts_body_without_id_and_requires_approval(): void
    {
        Http::fake(['*' => Http::response(['data' => ['id' => 3]])]);
        $tool = new CancelHandover($this->client());
        $request = new Request([
            'id' => 3,
            'note' => 'salah rack',
            'ringkasan' => 'Batalkan pemindahan ST-000003.',
        ]);

        $this->assertSame('Batalkan pemindahan ST-000003.', $tool->shouldRequestApproval($request)->reason);

        $tool->handle($request);

        Http::assertSent(function (HttpRequest $sent) {
            return $sent->method() === 'POST'
                && $sent->url() === 'http://wms.test/api/handovers/3/cancel'
                && $sent->data() === ['note' => 'salah rack'];
        });
    }

    public function test_create_tool_posts_items(): void
    {
        Http::fake(['*' => Http::response([], 201)]);
        $items = [['product_id' => 1, 'quantity' => 2, 'rack_from_id' => 3, 'rack_to_id' => 4]];

        $result = (new CreateHandover($this->client()))->handle(new Request(['items' => $items]));

        $this->assertSame(201, json_decode($result, true)['status']);
        Http::assertSent(fn (HttpRequest $sent) => $sent->url() === 'http://wms.test/api/handovers' && $sent['items'] === $items);
    }

    public function test_list_tool_returns_forbidden_as_text(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Tidak diizinkan.'], 403)]);

        $result = json_decode((new GetHandovers($this->client()))->handle(new Request), true);

        $this->assertSame(403, $result['status']);
        $this->assertSame('Tidak diizinkan.', $result['body']['message']);
    }

    public function test_list_tool_sends_filters_search_and_token(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $filters = [['field' => 'status', 'operator' => 'eq', 'value' => 'taken']];

        $result = (new GetHandovers($this->client()))->handle(new Request([
            'filters' => $filters,
            'search' => 'HO-1',
        ]));

        $this->assertSame(200, json_decode($result, true)['status']);
        Http::assertSent(function (HttpRequest $sent) {
            parse_str(parse_url($sent->url(), PHP_URL_QUERY), $query);

            return str_starts_with($sent->url(), 'http://wms.test/api/handovers?')
                && $sent->hasHeader('Authorization', 'Bearer token')
                && $query['search'] === 'HO-1'
                && $query['filters'][0]['value'] === 'taken'
                && $query['per_page'] === '10';
        });
    }

    public function test_show_tool_gets_by_id(): void
    {
        Http::fake(['*' => Http::response(['data' => ['id' => 9]])]);

        (new GetHandover($this->client()))->handle(new Request(['id' => 9]));

        Http::assertSent(fn (HttpRequest $sent) => $sent->url() === 'http://wms.test/api/handovers/9');
    }

    public function test_unreachable_wms_returns_503_text(): void
    {
        Http::fake(fn () => throw new ConnectionException('down'));

        $result = json_decode((new GetHandovers($this->client()))->handle(new Request), true);

        $this->assertSame(503, $result['status']);
        $this->assertSame('Sistem tidak dapat dihubungi.', $result['message']);
    }
}
