import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:23
* @route '/forgot-password'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/forgot-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:23
* @route '/forgot-password'
*/
show.url = (options?: RouteQueryOptions) => {




    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:23
* @route '/forgot-password'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:23
* @route '/forgot-password'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:23
* @route '/forgot-password'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:23
* @route '/forgot-password'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:23
* @route '/forgot-password'
*/
showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::sendOTP
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:31
* @route '/forgot-password'
*/
export const sendOTP = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendOTP.url(options),
    method: 'post',
})

sendOTP.definition = {
    methods: ["post"],
    url: '/forgot-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::sendOTP
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:31
* @route '/forgot-password'
*/
sendOTP.url = (options?: RouteQueryOptions) => {




    return sendOTP.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::sendOTP
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:31
* @route '/forgot-password'
*/
sendOTP.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendOTP.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::sendOTP
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:31
* @route '/forgot-password'
*/
const sendOTPForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sendOTP.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::sendOTP
* @see app/Http/Controllers/Auth/ForgotPasswordController.php:31
* @route '/forgot-password'
*/
sendOTPForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sendOTP.url(options),
    method: 'post',
})

sendOTP.form = sendOTPForm

const ForgotPasswordController = { show, sendOTP }

export default ForgotPasswordController