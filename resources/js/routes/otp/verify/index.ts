import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\OTPController::submit
* @see app/Http/Controllers/Auth/OTPController.php:43
* @route '/verify-otp'
*/
export const submit = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/verify-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\OTPController::submit
* @see app/Http/Controllers/Auth/OTPController.php:43
* @route '/verify-otp'
*/
submit.url = (options?: RouteQueryOptions) => {




    return submit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OTPController::submit
* @see app/Http/Controllers/Auth/OTPController.php:43
* @route '/verify-otp'
*/
submit.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::submit
* @see app/Http/Controllers/Auth/OTPController.php:43
* @route '/verify-otp'
*/
const submitForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: submit.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::submit
* @see app/Http/Controllers/Auth/OTPController.php:43
* @route '/verify-otp'
*/
submitForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: submit.url(options),
    method: 'post',
})

submit.form = submitForm



const verify = {
    submit: Object.assign(submit, submit),
}

export default verify