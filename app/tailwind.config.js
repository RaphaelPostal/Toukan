const colors = require('tailwindcss/colors')

module.exports = {
    purge: [],
    darkMode: false, // or 'media' or 'class'
    theme: {
        colors: {
            black: colors.black,
            white: colors.white,
            gray: colors.trueGray,
            indigo: colors.indigo,
            red: colors.red,
            yellow: colors.amber,
            'toukan' : '#F49B22',
            'toukan-dark' : '#D68402',
            'toukan-white' : '#FFEDD0',
            'red' : '#F70303',
            'light-blue' : '#1488F3',
            'blue' : '#575A89',
            'green' : '#04D733',
            'dark-gray' : '#2E2E2E',
            'light-gray' : '#EDEDED',
            'salmon' : '#FFDED0'
        },
        extend: {},
        fontFamily: {
            base: ['Karla', 'sans-serif'],
        },
        plugins: [
            require('@tailwindcss/forms'),
        ],
    },
    variants: {
        extend: {},
    },
    plugins: [
    ],
}