@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-12">Our Blog</h1>
    
    <div class="space-y-12">
        @foreach($posts as $post)
            <article class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <time>{{ $post['date'] }}</time>
                    </div>
                    
                    <h2 class="text-2xl font-semibold text-gray-900">
                        <a href="/post/{{ $post['slug'] }}" class="hover:text-emerald-600 transition-colors">
                            {{ $post['title'] }}
                        </a>
                    </h2>
                    
                    <p class="text-gray-600 leading-relaxed">{{ $post['description'] }}</p>
                    
                    <div class="pt-4">
                        <a href="/post/{{ $post['slug'] }}" 
                           class="inline-flex items-center text-emerald-600 font-medium hover:text-emerald-700">
                            Read article
                            <svg class="w-4 h-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if($posts->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <h2 class="text-2xl font-medium text-gray-900 mb-2">No blog posts yet</h2>
            <p class="text-gray-600">Stay tuned! We'll be sharing financial tips and insights soon.</p>
        </div>
    @endif
</div>
@endsection