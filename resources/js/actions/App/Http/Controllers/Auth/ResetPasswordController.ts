import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::reset
* @see app/Http/Controllers/Auth/ResetPasswordController.php:42
* @route '/reset-password'
*/
export const reset = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

reset.definition = {
    methods: ["post"],
    url: '/reset-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::reset
* @see app/Http/Controllers/Auth/ResetPasswordController.php:42
* @route '/reset-password'
*/
reset.url = (options?: RouteQueryOptions) => {




    return reset.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::reset
* @see app/Http/Controllers/Auth/ResetPasswordController.php:42
* @route '/reset-password'
*/
reset.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::reset
* @see app/Http/Controllers/Auth/ResetPasswordController.php:42
* @route '/reset-password'
*/
const resetForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reset.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::reset
* @see app/Http/Controllers/Auth/ResetPasswordController.php:42
* @route '/reset-password'
*/
resetForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reset.url(options),
    method: 'post',
})

reset.form = resetForm

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::show
* @see app/Http/Controllers/Auth/ResetPasswordController.php:25
* @route '/reset-password'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/reset-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::show
* @see app/Http/Controllers/Auth/ResetPasswordController.php:25
* @route '/reset-password'
*/
show.url = (options?: RouteQueryOptions) => {




    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::show
* @see app/Http/Controllers/Auth/ResetPasswordController.php:25
* @route '/reset-password'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::show
* @see app/Http/Controllers/Auth/ResetPasswordController.php:25
* @route '/reset-password'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::show
* @see app/Http/Controllers/Auth/ResetPasswordController.php:25
* @route '/reset-password'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::show
* @see app/Http/Controllers/Auth/ResetPasswordController.php:25
* @route '/reset-password'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\ResetPasswordController::show
* @see app/Http/Controllers/Auth/ResetPasswordController.php:25
* @route '/reset-password'
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

const ResetPasswordController = { reset, show }

export default ResetPasswordController