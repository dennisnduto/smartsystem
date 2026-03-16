<div>
     @include('layouts.navigation')

     @isset($header)
         <header class="bg-white shadow-sm border-b border-gray-200">
             <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                 {{ $header }}
             </div>
         </header>
     @endisset

     <main class="py-6">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             {{ $slot }}
         </div>
     </main>
 </div>
