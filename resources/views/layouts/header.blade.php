<header class="max-w-4xl mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded shadow">
    <h1 class="text-xl font-bold text-indigo-600">📚 TechBookStore</h1>
    
    <nav class="flex items-center space-x-4 text-sm">
        <a href="{{ route('home') }}" class="hover:underline">Home</a>
        <a href="{{ route('books.index') }}" class="hover:underline">All Books</a>

        @auth
            <!-- Visible only when logged in -->
            <a href="{{ route('books.create') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded hover:bg-indigo-700">
                + Add Book
            </a>
            
            <span class="text-gray-500 font-medium">Hello, {{ Auth::user()->name }}</span>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-rose-600 hover:underline bg-transparent border-none p-0 cursor-pointer">
                    Logout
                </button>
            </form>
        @endauth

        @guest
            <!-- Visible only when logged out -->
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">Login</a>
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded hover:bg-indigo-700">
                Register
            </a>
        @endguest
    </nav>
</header>

