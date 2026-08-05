<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Landing Page</h2>
            <p class="text-sm text-slate-400">Edit the copy shown on the public homepage</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="mb-4 rounded-xl bg-emerald-500/15 px-4 py-3 text-sm font-medium text-emerald-300">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.landing-page.update') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Hero</h3>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Badge text</span>
                            <input type="text" name="hero_badge_text" value="{{ old('hero_badge_text', $content->hero_badge_text) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('hero_badge_text')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Headline</span>
                            <input type="text" name="hero_headline" value="{{ old('hero_headline', $content->hero_headline) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('hero_headline')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Subtext</span>
                            <textarea name="hero_subtext" rows="3" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>{{ old('hero_subtext', $content->hero_subtext) }}</textarea>
                            <x-input-error :messages="$errors->get('hero_subtext')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Button text</span>
                            <input type="text" name="hero_cta_text" value="{{ old('hero_cta_text', $content->hero_cta_text) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('hero_cta_text')" />
                        </label>
                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-medium text-slate-300">Hero image</span>
                            @if ($content->hero_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="Current hero image" class="h-32 w-full max-w-sm rounded-lg object-cover">
                            @endif
                            <input type="file" name="hero_image" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-white">
                            <span class="text-xs text-slate-500">Leave blank to keep the current image. Capped at 2MB.</span>
                            <x-input-error :messages="$errors->get('hero_image')" />
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Heading sizes</h3>
                    <p class="mt-1 text-sm text-slate-400">Adjust how large each heading appears on the homepage. Sizes scale smoothly with screen width.</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach (\App\Models\LandingPageContent::HEADING_KEYS as $key => $heading)
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-300">{{ $heading['label'] }}</span>
                                <select name="heading_sizes[{{ $key }}]" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                    @foreach (array_keys(\App\Models\LandingPageContent::HEADING_SIZES) as $size)
                                        <option value="{{ $size }}" @selected(old("heading_sizes.$key", $content->heading_sizes[$key] ?? $heading['default']) === $size)>{{ strtoupper($size) }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endforeach
                    </div>
                    <x-array-errors field="heading_sizes" class="mt-2" />

                    <div class="mt-6 border-t border-white/10 pt-6">
                        <p class="text-sm font-medium text-slate-200">Image/text sections — desktop only</p>
                        <p class="mt-1 text-sm text-slate-400">Exact pixel size for the heading and description text in these full-width sections, at desktop screen widths. Mobile is unaffected. Heading size covers Requirements, How it works, and Payment links (the Hero headline uses the Heading sizes section above instead); Description size also covers the Hero subtext.</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-300">Heading size (px)</span>
                                <input type="number" name="section_heading_size_px" value="{{ old('section_heading_size_px', $content->section_heading_size_px) }}" min="12" max="120" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                <x-input-error :messages="$errors->get('section_heading_size_px')" />
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-300">Description size (px)</span>
                                <input type="number" name="section_description_size_px" value="{{ old('section_description_size_px', $content->section_description_size_px) }}" min="10" max="60" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                <x-input-error :messages="$errors->get('section_description_size_px')" />
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Payment method logos: dynamic, add as many as needed --}}
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10" x-data="{ items: @js(old('payment_logos', $content->payment_logos ?? [])) }">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Payment method logos</h3>
                            <p class="mt-1 text-sm text-slate-400">Leave an image blank to keep a styled text placeholder for that logo.</p>
                        </div>
                        <button type="button" @click="items.push({ label: '', image_path: null })" class="shrink-0 rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">+ Add logo</button>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-xl bg-slate-800/60 p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <label class="flex flex-1 flex-col gap-1 text-sm">
                                        <span class="font-medium text-slate-300">Label</span>
                                        <input type="text" :name="`payment_logos[${index}][label]`" x-model="item.label" placeholder="Optional if you upload an image" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                    </label>
                                    <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="mt-5 shrink-0 rounded-full border border-rose-400/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                                </div>
                                <label class="flex flex-col gap-2 text-sm">
                                    <span class="font-medium text-slate-300">Logo image</span>
                                    <template x-if="item.image_path">
                                        <img :src="'/storage/' + item.image_path" :alt="item.label" class="h-10 w-auto max-w-[8rem] bg-white object-contain p-1">
                                    </template>
                                    <input type="hidden" :name="`payment_logos[${index}][image_path]`" :value="item.image_path">
                                    <input type="file" :name="`payment_logos[${index}][image]`" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-white">
                                </label>
                            </div>
                        </template>
                    </div>
                    <x-array-errors field="payment_logos" class="mt-2" />
                </div>

                {{-- Requirements: dynamic, add as many as needed --}}
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10" x-data="{ items: @js(old('requirements', $content->requirements ?? [])) }">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Requirements</h3>
                            <p class="mt-1 text-sm text-slate-400">Who can use SentePro — shown immediately below the payment logos on the homepage, as full alternating image/text sections.</p>
                        </div>
                        <button type="button" @click="items.push({ title: '', description: '', type: '', image_path: null })" class="shrink-0 rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">+ Add requirement</button>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Section heading</span>
                            <input type="text" name="requirements_heading" value="{{ old('requirements_heading', $content->requirements_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('requirements_heading')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Section subtext</span>
                            <input type="text" name="requirements_subtext" value="{{ old('requirements_subtext', $content->requirements_subtext) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('requirements_subtext')" />
                        </label>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-xl bg-slate-800/60 p-4 grid gap-3 md:grid-cols-[1fr_1fr_2fr_auto] md:items-end">
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Title</span>
                                    <input type="text" :name="`requirements[${index}][title]`" x-model="item.title" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Register button links to</span>
                                    <select :name="`requirements[${index}][type]`" x-model="item.type" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                        @foreach (\App\Models\LandingPageContent::REQUIREMENT_TYPE_OPTIONS as $typeValue => $typeLabel)
                                            <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Description</span>
                                    <input type="text" :name="`requirements[${index}][description]`" x-model="item.description" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="shrink-0 rounded-full border border-rose-400/30 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                                <label class="flex flex-col gap-2 text-sm md:col-span-5">
                                    <span class="font-medium text-slate-300">Section image</span>
                                    <template x-if="item.image_path">
                                        <img :src="'/storage/' + item.image_path" :alt="item.title" class="h-24 w-full max-w-sm rounded-lg object-cover">
                                    </template>
                                    <input type="hidden" :name="`requirements[${index}][image_path]`" :value="item.image_path">
                                    <input type="file" :name="`requirements[${index}][image]`" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-white">
                                    <span class="text-xs text-slate-500">Leave blank to fall back to the hero image, then an illustration.</span>
                                </label>
                            </div>
                        </template>
                    </div>
                    <x-array-errors field="requirements" class="mt-2" />
                </div>

                {{-- Features: dynamic, add as many as needed --}}
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10" x-data="{ items: @js(old('features', $content->features ?? [])) }">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">Features</h3>
                        <button type="button" @click="items.push({ title: '', description: '' })" class="shrink-0 rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">+ Add feature</button>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Section heading</span>
                            <input type="text" name="features_heading" value="{{ old('features_heading', $content->features_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('features_heading')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Section subtext</span>
                            <input type="text" name="features_subtext" value="{{ old('features_subtext', $content->features_subtext) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('features_subtext')" />
                        </label>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-xl bg-slate-800/60 p-4 grid gap-3 md:grid-cols-[1fr_2fr_auto] md:items-end">
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Title</span>
                                    <input type="text" :name="`features[${index}][title]`" x-model="item.title" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Description</span>
                                    <input type="text" :name="`features[${index}][description]`" x-model="item.description" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="shrink-0 rounded-full border border-rose-400/30 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                            </div>
                        </template>
                    </div>
                    <x-array-errors field="features" class="mt-2" />
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">"Payment links &amp; QR codes" section</h3>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Heading</span>
                            <input type="text" name="payment_links_heading" value="{{ old('payment_links_heading', $content->payment_links_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('payment_links_heading')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Subtext</span>
                            <input type="text" name="payment_links_subtext" value="{{ old('payment_links_subtext', $content->payment_links_subtext) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('payment_links_subtext')" />
                        </label>
                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-medium text-slate-300">Image</span>
                            @if ($content->payment_links_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->payment_links_image_path) }}" alt="Current payment links image" class="h-32 w-full max-w-sm rounded-lg object-cover">
                            @endif
                            <input type="file" name="payment_links_image" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-white">
                            <span class="text-xs text-slate-500">Leave blank to keep the current image. Capped at 2MB.</span>
                            <x-input-error :messages="$errors->get('payment_links_image')" />
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">"One dashboard for every balance" section</h3>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Heading</span>
                            <input type="text" name="balances_heading" value="{{ old('balances_heading', $content->balances_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('balances_heading')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Subtext</span>
                            <input type="text" name="balances_subtext" value="{{ old('balances_subtext', $content->balances_subtext) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('balances_subtext')" />
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10" x-data="{ items: @js(old('how_it_works_steps', $content->how_it_works_steps ?? [])) }">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">"It's simple to start" section</h3>
                        <button type="button" @click="items.push({ title: '', description: '' })" class="shrink-0 rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">+ Add step</button>
                    </div>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Heading</span>
                            <input type="text" name="how_it_works_heading" value="{{ old('how_it_works_heading', $content->how_it_works_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('how_it_works_heading')" />
                        </label>
                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-medium text-slate-300">Image</span>
                            @if ($content->how_it_works_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->how_it_works_image_path) }}" alt="Current how-it-works image" class="h-32 w-full max-w-sm rounded-lg object-cover">
                            @endif
                            <input type="file" name="how_it_works_image" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-white">
                            <span class="text-xs text-slate-500">Leave blank to keep the current image. Capped at 2MB.</span>
                            <x-input-error :messages="$errors->get('how_it_works_image')" />
                        </label>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-xl bg-slate-800/60 p-4 grid gap-3 md:grid-cols-[auto_1fr_2fr_auto] md:items-end">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300" x-text="index + 1"></span>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Title</span>
                                    <input type="text" :name="`how_it_works_steps[${index}][title]`" x-model="item.title" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Description</span>
                                    <input type="text" :name="`how_it_works_steps[${index}][description]`" x-model="item.description" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="shrink-0 rounded-full border border-rose-400/30 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                            </div>
                        </template>
                    </div>
                    <x-array-errors field="how_it_works_steps" class="mt-2" />
                    <label class="mt-4 flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-300">Button text</span>
                        <input type="text" name="how_it_works_cta_text" value="{{ old('how_it_works_cta_text', $content->how_it_works_cta_text) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        <x-input-error :messages="$errors->get('how_it_works_cta_text')" />
                    </label>
                </div>

                {{-- FAQ: dynamic, add as many as needed --}}
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10" x-data="{ items: @js(old('faqs', $content->faqs ?? [])) }">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">FAQ</h3>
                        <button type="button" @click="items.push({ question: '', answer: '' })" class="shrink-0 rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">+ Add question</button>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Section heading</span>
                            <input type="text" name="faq_heading" value="{{ old('faq_heading', $content->faq_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('faq_heading')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Section subtext</span>
                            <input type="text" name="faq_subtext" value="{{ old('faq_subtext', $content->faq_subtext) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('faq_subtext')" />
                        </label>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-xl bg-slate-800/60 p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <label class="flex flex-1 flex-col gap-1 text-sm">
                                        <span class="font-medium text-slate-300">Question</span>
                                        <input type="text" :name="`faqs[${index}][question]`" x-model="item.question" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                    </label>
                                    <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="mt-5 shrink-0 rounded-full border border-rose-400/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                                </div>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Answer</span>
                                    <textarea :name="`faqs[${index}][answer]`" x-model="item.answer" rows="2" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required></textarea>
                                </label>
                            </div>
                        </template>
                    </div>
                    <x-array-errors field="faqs" class="mt-2" />
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">"Supported payment ecosystem" section</h3>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Heading</span>
                            <input type="text" name="gateways_heading" value="{{ old('gateways_heading', $content->gateways_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('gateways_heading')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Subtext</span>
                            <input type="text" name="gateways_subtext" value="{{ old('gateways_subtext', $content->gateways_subtext) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('gateways_subtext')" />
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Business registration page</h3>
                    <p class="mt-1 text-sm text-slate-400">The "What are you registering?" step shown on /business/register before the form fields.</p>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Prompt heading</span>
                            <input type="text" name="register_prompt_heading" value="{{ old('register_prompt_heading', $content->register_prompt_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('register_prompt_heading')" />
                        </label>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="space-y-3 rounded-xl bg-slate-800/60 p-4">
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Individual card</p>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Title</span>
                                    <input type="text" name="register_individual_title" value="{{ old('register_individual_title', $content->register_individual_title) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                    <x-input-error :messages="$errors->get('register_individual_title')" />
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Description</span>
                                    <input type="text" name="register_individual_description" value="{{ old('register_individual_description', $content->register_individual_description) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                    <x-input-error :messages="$errors->get('register_individual_description')" />
                                </label>
                            </div>
                            <div class="space-y-3 rounded-xl bg-slate-800/60 p-4">
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Business card</p>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Title</span>
                                    <input type="text" name="register_business_title" value="{{ old('register_business_title', $content->register_business_title) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                    <x-input-error :messages="$errors->get('register_business_title')" />
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Description</span>
                                    <input type="text" name="register_business_description" value="{{ old('register_business_description', $content->register_business_description) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                    <x-input-error :messages="$errors->get('register_business_description')" />
                                </label>
                            </div>
                            <div class="space-y-3 rounded-xl bg-slate-800/60 p-4">
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Non-Profit card</p>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Title</span>
                                    <input type="text" name="register_ngo_title" value="{{ old('register_ngo_title', $content->register_ngo_title) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                    <x-input-error :messages="$errors->get('register_ngo_title')" />
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Description</span>
                                    <input type="text" name="register_ngo_description" value="{{ old('register_ngo_description', $content->register_ngo_description) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                    <x-input-error :messages="$errors->get('register_ngo_description')" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Footer</h3>
                    <p class="mt-1 text-sm text-slate-400">Shown in the site footer. Leave a Contact field blank to hide that section.</p>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Tagline</span>
                            <input type="text" name="footer_tagline" value="{{ old('footer_tagline', $content->footer_tagline) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('footer_tagline')" />
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-300">Contact location</span>
                                <input type="text" name="contact_location" value="{{ old('contact_location', $content->contact_location) }}" placeholder="e.g. Kampala, Uganda" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                <x-input-error :messages="$errors->get('contact_location')" />
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-300">Contact phone number</span>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', $content->contact_phone) }}" placeholder="e.g. +256 700 000000" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                <x-input-error :messages="$errors->get('contact_phone')" />
                            </label>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">CTA banner</h3>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Heading</span>
                            <input type="text" name="cta_banner_heading" value="{{ old('cta_banner_heading', $content->cta_banner_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Subtext</span>
                            <textarea name="cta_banner_subtext" rows="2" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>{{ old('cta_banner_subtext', $content->cta_banner_subtext) }}</textarea>
                        </label>
                    </div>
                </div>

                <button type="submit" class="rounded-xl bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300">Save changes</button>
            </form>
        </div>
    </div>
</x-app-layout>
