<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertEventRequest;
use App\Models\Event;
use App\Models\Hall;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $events = Event::query()
            ->withCount('performances')
            ->when($request->string('q')->isNotEmpty(), fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%'))
            ->when($request->input('type'), fn ($query, string $type) => $query->where('type', $type))
            ->when($request->input('status'), function ($query, string $status): void {
                match ($status) {
                    'published' => $query->published(),
                    'scheduled' => $query->where('published_at', '>', now()),
                    'draft' => $query->whereNull('published_at'),
                    default => null,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(UpsertEventRequest $request): RedirectResponse
    {
        $event = Event::query()->create($this->eventAttributes($request));

        return redirect()->route('admin.events.show', $event)->with('success', 'رویداد ساخته شد؛ سانس‌های آن را اضافه کنید.');
    }

    public function show(Event $event): View
    {
        return view('admin.events.show', ['event' => $event->load('performances.hall.venue'), 'halls' => Hall::query()->with('venue')->where('capacity', '>', 0)->get()]);
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(UpsertEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($this->eventAttributes($request));

        return redirect()->route('admin.events.show', $event)->with('success', 'اطلاعات و تنظیمات انتشار رویداد به‌روزرسانی شد.');
    }

    public function togglePublication(Event $event): RedirectResponse
    {
        $event->update(['published_at' => $event->isPublished() ? null : now()]);

        return back()->with('success', $event->fresh()->isPublished() ? 'رویداد اکنون برای کاربران فعال است.' : 'رویداد غیرفعال و به پیش‌نویس منتقل شد.');
    }

    /** @return array<string, mixed> */
    private function eventAttributes(UpsertEventRequest $request): array
    {
        $attributes = $request->safe()->except(['publication_mode', 'scheduled_publish_at']);
        $attributes['published_at'] = match ($request->validated('publication_mode')) {
            'now' => now(),
            'scheduled' => $request->date('scheduled_publish_at'),
            default => null,
        };

        return $attributes;
    }
}
