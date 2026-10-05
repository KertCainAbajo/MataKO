<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Tip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TipController extends Controller
{
    /** @var array<string, string> Admin tabs and their labels. */
    private const TABS = [
        'student' => 'Student Tips',
        'professional' => 'Professional Tips',
        'daily_tip' => "Today's Eye Tip",
        'fact' => 'Did You Know?',
        'topics' => 'Eye Health Topics',
        'exercises' => 'Exercises',
        'results' => 'Result Screens',
    ];

    /** @var array<string, string> Top-level kind listed on each tab. */
    private const TAB_KINDS = [
        'student' => 'category', 'professional' => 'category', 'daily_tip' => 'daily_tip', 'fact' => 'fact',
        'topics' => 'topic', 'exercises' => 'exercise', 'results' => 'result',
    ];

    public function index(Request $request): View
    {
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'student';
        $kind = self::TAB_KINDS[$tab];

        $items = Tip::where('kind', $kind)
            ->when($kind === 'category', fn ($query) => $query->where('audience', $tab))
            ->with('children')
            ->orderBy('position')->orderBy('id')
            ->get();

        return view('admin.tips.index', ['tabs' => self::TABS, 'tab' => $tab, 'kind' => $kind, 'items' => $items]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $kind = array_key_exists($request->query('kind'), Tip::KINDS) ? $request->query('kind') : 'fact';
        if (Tip::KINDS[$kind]['fixed'] ?? false) {
            return redirect()->route('admin.tips.index', ['tab' => 'results']);
        }

        $parent = isset(Tip::KINDS[$kind]['parent']) ? Tip::where('kind', Tip::KINDS[$kind]['parent'])->find($request->query('parent')) : null;
        $tip = new Tip([
            'kind' => $kind,
            'audience' => $parent?->audience ?? $request->query('audience', $kind === 'category' ? 'student' : 'all'),
            'parent_id' => $parent?->id,
            'is_active' => true,
            'meta' => [],
            'translations' => [],
        ]);
        $tip->setRelation('parent', $parent);

        return view('admin.tips.form', ['tip' => $tip, 'parents' => $this->possibleParents($kind)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $kind = $request->validate(['kind' => ['required', Rule::in(array_keys(Tip::KINDS))]])['kind'];
        abort_if(Tip::KINDS[$kind]['fixed'] ?? false, 403);

        $tip = new Tip(['kind' => $kind]);
        $tip->fill($this->validated($request, $tip));
        $tip->position = (int) Tip::where('kind', $kind)->where('parent_id', $tip->parent_id)->max('position') + 1;
        $tip->save();
        AdminActivity::record('content.created', 'Added '.$tip->kindLabel().' "'.$this->label($tip).'"');

        return redirect()->route('admin.tips.index', ['tab' => $this->tabFor($tip)])->with('status', 'Content added. The app shows it the next time that screen is opened.');
    }

    public function edit(Tip $tip): View
    {
        $tip->load('parent');

        return view('admin.tips.form', ['tip' => $tip, 'parents' => $this->possibleParents($tip->kind)]);
    }

    public function update(Request $request, Tip $tip): RedirectResponse
    {
        $tip->update($this->validated($request, $tip));
        AdminActivity::record('content.updated', 'Updated '.$tip->kindLabel().' "'.$this->label($tip).'"');

        return redirect()->route('admin.tips.index', ['tab' => $this->tabFor($tip)])->with('status', 'Content saved.');
    }

    public function destroy(Tip $tip): RedirectResponse
    {
        abort_if(Tip::KINDS[$tip->kind]['fixed'] ?? false, 403);

        $tab = $this->tabFor($tip);
        $hadChildren = $tip->children()->exists();
        $tip->delete();
        AdminActivity::record('content.deleted', 'Deleted '.$tip->kindLabel().' "'.$this->label($tip).'"');

        return redirect()->route('admin.tips.index', ['tab' => $tab])->with('status', $hadChildren ? 'Deleted, together with everything inside it.' : 'Content deleted.');
    }

    /**
     * Swap an item with its neighbour above or below, among items of the same kind and parent.
     */
    public function move(Request $request, Tip $tip): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $siblings = Tip::where('kind', $tip->kind)
            ->where('parent_id', $tip->parent_id)
            ->when($tip->kind === 'category', fn ($query) => $query->where('audience', $tip->audience))
            ->orderBy('position')->orderBy('id')->get()->values();
        $index = $siblings->search(fn (Tip $item): bool => $item->is($tip));
        $swapWith = $siblings->get($direction === 'up' ? $index - 1 : $index + 1);

        if ($swapWith) {
            $ordered = $siblings->all();
            [$ordered[$index], $ordered[$siblings->search($swapWith)]] = [$swapWith, $tip];
            foreach ($ordered as $position => $item) {
                $item->update(['position' => $position + 1]);
            }
        }

        return back();
    }

    /**
     * Validate the fields this kind of content has, split into columns, meta and translations.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, Tip $tip): array
    {
        $config = Tip::KINDS[$tip->kind];
        $parentKind = $config['parent'] ?? null;

        $rules = [
            'is_active' => ['boolean'],
            'parent_id' => $parentKind ? ['required', Rule::exists('tips', 'id')->where('kind', $parentKind)] : ['prohibited'],
            'audience' => match ($tip->kind) {
                'category' => ['required', Rule::in(['student', 'professional'])],
                'daily_tip', 'fact' => ['required', Rule::in(Tip::AUDIENCES)],
                default => ['prohibited'],
            },
        ];
        foreach ($config['fields'] as $name => [$label, $type]) {
            $rules[$name] = match ($type) {
                'text' => [in_array($name, ['title', 'body'], true) ? 'required' : 'nullable', 'string', 'max:160'],
                'textarea' => [$name === 'body' ? 'required' : 'nullable', 'string', 'max:600'],
                'icon' => ['nullable', 'string', 'max:40', 'regex:/^[a-z0-9-]+$/'],
                'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'number' => ['required', 'integer', 'min:5', 'max:3600'],
                'checkbox' => ['boolean'],
            };
            if (in_array($type, ['text', 'textarea'], true)) {
                foreach (array_keys(Tip::LANGUAGES) as $code) {
                    $rules["translations.{$code}.{$name}"] = ['nullable', 'string', 'max:600'];
                }
            }
        }
        $data = $request->validate($rules, [], $this->attributeNames($config['fields']));

        $values = ['is_active' => $request->boolean('is_active'), 'meta' => $tip->meta ?? []];
        foreach ($config['fields'] as $name => [$label, $type]) {
            $value = $type === 'checkbox' ? $request->boolean($name) : ($data[$name] ?? null);
            if (in_array($name, Tip::COLUMN_FIELDS, true)) {
                $values[$name] = $value;
            } else {
                $values['meta'][$name] = $type === 'number' ? (int) $value : $value;
            }
        }

        $translations = [];
        foreach (array_keys(Tip::LANGUAGES) as $code) {
            $translations[$code] = array_filter($data['translations'][$code] ?? [], fn ($value): bool => is_string($value) && trim($value) !== '');
        }
        $values['translations'] = array_filter($translations);

        if ($parentKind) {
            $parent = Tip::findOrFail($data['parent_id']);
            $values['parent_id'] = $parent->id;
            $values['audience'] = $parent->audience;
        } else {
            $values['parent_id'] = null;
            $values['audience'] = $data['audience'] ?? $tip->audience ?? 'all';
        }

        return $values;
    }

    /**
     * @param  array<string, array{0: string, 1: string}>  $fields
     * @return array<string, string>
     */
    private function attributeNames(array $fields): array
    {
        $names = [];
        foreach ($fields as $name => [$label]) {
            $names[$name] = Str::lower($label);
            foreach (Tip::LANGUAGES as $code => $language) {
                $names["translations.{$code}.{$name}"] = Str::lower($label)." ({$language})";
            }
        }

        return $names;
    }

    /**
     * @return Collection<int, Tip>
     */
    private function possibleParents(string $kind): Collection
    {
        $parentKind = Tip::KINDS[$kind]['parent'] ?? null;

        return $parentKind ? Tip::where('kind', $parentKind)->orderBy('audience')->orderBy('position')->get() : collect();
    }

    private function tabFor(Tip $tip): string
    {
        $tab = Tip::KINDS[$tip->kind]['tab'];

        return $tab === 'audience' ? $tip->audience : $tab;
    }

    private function label(Tip $tip): string
    {
        return $tip->title ?: Str::limit((string) $tip->body, 50);
    }
}
