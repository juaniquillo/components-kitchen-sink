<?php


return [
    // Simple
    // https://preline.co/docs/input.html
    'wrapper' => 'mt-3 p-3 bg-sky-200 border border-sky-300 rounded',
    'wrapper-dark' => 'dark:bg-sky-950 dark:border-sky-900',
    
    'text' => 'py-3 px-4 bg-gray-200 block border border-gray-400 rounded-lg focus-visible:border-blue-500 focus-visible:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none  placeholder-gray-400',
    'text-dark' => 'dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400 dark:placeholder-gray-600 dark:focus-visible:ring-gray-600',

    'checkbox' => 'bg-gray-200 border border-gray-400 checked:bg-blue-500 rounded text-blue-600 focus-visible:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none',
    'checkbox-dark' => 'dark:bg-gray-900 dark:border-gray-600 dark:checked:border-blue-500 dark:focus-visible:ring-offset-gray-800',
    
    'radio' => 'border-gray-300 rounded-full text-blue-600 focus-visible:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none ',
    'radio-dark' => 'dark:bg-gray-900 dark:border-gray-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus-visible:ring-offset-gray-800',

    'file' => 'p-3 bg-gray-200 block border border-gray-400 rounded-lg focus-visible:border-blue-500 focus-visible:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none  placeholder-gray-400',
    'file-dark' => 'dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400 dark:placeholder-gray-600 dark:focus-visible:ring-gray-600',
    'file-primary' => 'file:bg-blue-500 file:text-white file:py-2 file:px-4 w-full',

    'textarea' => 'py-3 px-4 bg-gray-200 block border border-gray-400 rounded-lg text-sm focus-visible:border-blue-500 focus-visible:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none',
    'textarea-dark' => ' dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400 dark:placeholder-gray-600 dark:focus-visible:ring-gray-600',

    // Columns
    // https://pagedone.io/docs/formss (with some modifications)
    'text-columns' => 'h-11 px-5 py-2.5 bg-white leading-7 text-base font-normal shadow-xs text-gray-900 bg-transparent border border-gray-400 rounded-full placeholder-gray-400',
    'text-columns-dark' => 'dark:bg-gray-400',

    // With model
    // https://tailwindflex.com/@lukas-muller/contact-us-page-template
    'with-model' => 'bg-gray-200 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-bold leading-tight focus-visible:outline-blue-500 focus-visible:outline-offset-2 disabled:opacity-50 placeholder:text-gray-400',

    'with-model-checkbox' => 'appearance-none rounded bg-gray-200 dark:bg-gray-200 w-5 h-5 text-white focus-visible:outline-blue-500 focus-visible:outline-offset-2 checked:bg-indigo-800 disabled:opacity-50
    focus:ring-indigo-800 focus:ring-offset-1 focus:ring-2 ',

    'with-model-button' => 'shadow bg-indigo-600 bg-indigo-200 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus-visible:outline-blue-500 focus-visible:outline-offset-2 disabled:opacity-50 ',

    'with-model-wrapper' => 'text-white relative p-8 bg-indigo-500 shadow-lg sm:rounded-3xl ',
    'with-model-checkbox-container' => 'grid gap-2 grid-cols-[auto_1fr] items-center',
    'with-model-checkbox-error' => 'col-span-2',

    // Floating labels
    // https://www.creative-tim.com/twcomponents/component/floating-form-labels-5
    'floating-container' => 'relative z-0 w-full mb-5',

    'floating-label' => 'px-2 absolute left-0 ml-1 -top-3 duration-100 ease-linear peer-placeholder-shown:top-3 peer-focus:-top-3 ',
    'floating-label-dark' => 'dark:text-gray-300',

    'floating-input' => 'peer px-3 py-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 border-b-gray-400 appearance-none focus:outline-none focus:ring-0 focus:border-black border-gray-200 placeholder:text-[#d1d5db]',
    'floating-input-dark' => ' dark:border-gray-200 dark:focus:border-gray-400 dark:placeholder:text-[#192333]',

    'floating-error' => 'text-sm text-red-600 hidden',

];
//<span class=" font-bold  rounded-md accent-"></span>
