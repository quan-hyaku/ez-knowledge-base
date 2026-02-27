@extends('kb::layout')

@section('title', 'Submit a Support Ticket')

@section('content')
<div class="py-4">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-sm mb-8 text-slate-500 dark:text-slate-400">
        <a class="hover:text-primary transition-colors" href="{{ route('kb.landing') }}">Home</a>
        <span class="material-icons text-xs">chevron_right</span>
        <a class="hover:text-primary transition-colors" href="#">Support</a>
        <span class="material-icons text-xs">chevron_right</span>
        <span class="text-slate-900 dark:text-white font-medium">Submit a Ticket</span>
    </nav>

    <!-- Main Grid -->
    <div class="grid lg:grid-cols-12 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 sm:p-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Submit a Support Request</h1>
                    <p class="text-slate-500 dark:text-slate-400">Please provide as much detail as possible so we can assist you better.</p>
                </div>

                <form action="{{ route('kb.ticket.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                    @csrf

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">Subject</label>
                        <input
                            type="text"
                            id="subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            placeholder="What is your issue about?"
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                        />
                        @error('subject')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category and Urgency Row -->
                    <div class="grid sm:grid-cols-2 gap-6">
                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">Category</label>
                            <select
                                id="category"
                                name="category"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                            >
                                <option value="">Select a category</option>
                                <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>Billing</option>
                                <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>Technical Issue</option>
                                <option value="feature" {{ old('category') === 'feature' ? 'selected' : '' }}>Feature Request</option>
                                <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General Inquiry</option>
                                <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Urgency -->
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-white mb-3">Urgency</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="urgency"
                                        value="low"
                                        {{ old('urgency') === 'low' || old('urgency') === null ? 'checked' : '' }}
                                        class="w-4 h-4 text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Low</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="urgency"
                                        value="medium"
                                        {{ old('urgency') === 'medium' ? 'checked' : '' }}
                                        class="w-4 h-4 text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Medium</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="urgency"
                                        value="high"
                                        {{ old('urgency') === 'high' ? 'checked' : '' }}
                                        class="w-4 h-4 text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">High</span>
                                </label>
                            </div>
                            @error('urgency')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">Description</label>
                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-800">
                            <!-- Toolbar -->
                            <div class="border-b border-slate-200 dark:border-slate-700 p-3 flex gap-1 bg-slate-50 dark:bg-slate-900/50">
                                <button type="button" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded transition-colors" title="Bold">
                                    <span class="material-icons text-sm">format_bold</span>
                                </button>
                                <button type="button" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded transition-colors" title="Italic">
                                    <span class="material-icons text-sm">format_italic</span>
                                </button>
                                <button type="button" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded transition-colors" title="Underline">
                                    <span class="material-icons text-sm">format_underlined</span>
                                </button>
                                <div class="w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>
                                <button type="button" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded transition-colors" title="List">
                                    <span class="material-icons text-sm">format_list_bulleted</span>
                                </button>
                                <button type="button" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded transition-colors" title="Link">
                                    <span class="material-icons text-sm">link</span>
                                </button>
                            </div>
                            <!-- Textarea -->
                            <textarea
                                id="description"
                                name="description"
                                rows="8"
                                placeholder="Please describe your issue in detail..."
                                class="w-full px-4 py-3 text-slate-900 dark:text-white bg-white dark:bg-slate-800 focus:outline-none resize-none"
                            >{{ old('description') }}</textarea>
                        </div>
                        @error('description')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-white mb-2">Attachments (Optional)</label>
                        <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-lg p-8 text-center hover:border-primary/50 hover:bg-primary/5 transition-colors cursor-pointer group">
                            <input
                                type="file"
                                name="attachments[]"
                                multiple
                                class="hidden"
                                id="file-upload"
                            />
                            <label for="file-upload" class="cursor-pointer">
                                <div class="flex justify-center mb-3">
                                    <span class="material-icons text-4xl text-slate-400 group-hover:text-primary/50 transition-colors">cloud_upload</span>
                                </div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white mb-1">Drag and drop your files here</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">or <span class="text-primary font-medium">click to browse</span></p>
                            </label>
                        </div>
                        @error('attachments')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name and Email -->
                    <div class="grid sm:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">Full Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Your full name"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-900 dark:text-white mb-2">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="your@email.com"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                            />
                            @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button and Info -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4">
                        <button
                            type="submit"
                            class="px-6 py-2.5 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20"
                        >
                            Submit Ticket
                        </button>
                        <p class="text-sm text-slate-600 dark:text-slate-400 flex items-center gap-2">
                            <span class="material-icons text-sm text-primary">schedule</span>
                            Average response time: Under 24 hours
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-4 sticky top-24">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="material-icons text-primary">lightbulb</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white">Maybe these help?</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Relevant documentation</p>
                    </div>
                </div>

                <!-- Articles List -->
                <div class="p-6 space-y-4">
                    @forelse($featuredArticles ?? collect() as $article)
                        <a class="block p-4 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-transparent hover:border-primary/30 hover:bg-white dark:hover:bg-slate-800 transition-all group" href="{{ route('kb.article', [$article->category->slug, $article->slug]) }}">
                            <h3 class="font-semibold text-sm text-slate-900 dark:text-white mb-1">{{ $article->title }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">{{ $article->excerpt }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500 text-center">No articles available</p>
                    @endforelse

                    <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-center text-xs text-slate-500 dark:text-slate-400">
                            Still can't find what you need?<br/>
                            <span class="text-primary font-medium">Continue with your ticket submission.</span>
                        </p>
                    </div>
                </div>

                <!-- Live Chat CTA -->
                <div class="bg-slate-50 dark:bg-slate-800/80 p-6 flex flex-col items-center gap-3">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-900 bg-primary/20 flex items-center justify-center">
                            <span class="material-icons text-primary text-sm">person</span>
                        </div>
                        <div class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-900 bg-primary/30 flex items-center justify-center text-[10px] font-bold text-primary">+</div>
                    </div>
                    <p class="text-xs text-center text-slate-600 dark:text-slate-400">Our agents are online and ready to help.</p>
                    <a href="#" class="text-primary text-xs font-bold hover:underline">Start Live Chat Instead</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
