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
            red: colors.rose,
            yellow: colors.amber,

            'toukan' : '#F49B22',
            'toukan-dark' : '#D68402',
            'toukan-white' : '#FFEDD0',
            'dark-gray' : '#2E2E2E',
            'light-gray' : '#EDEDED',
            'salmon' : '#FFDED0'
        },
        extend: {},
        fontFamily: {
            base: ['Karla', 'sans-serif'],
        },
    },
    variants: {
        extend: {},
    },
    plugins: [
    ],
}