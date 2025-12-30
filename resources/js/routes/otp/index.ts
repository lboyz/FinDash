import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import verify8ef1b2 from './verify'
/**
* @see \App\Http\Controllers\Auth\OTPController::verify
* @see app/Http/Controllers/Auth/OTPController.php:25
* @route '/verify-otp'
*/
export const verify = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verify.url(options),
    method: 'get',
})

verify.definition = {
    methods: ["get","head"],
    url: '/verify-otp',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\OTPController::verify
* @see app/Http/Controllers/Auth/OTPController.php:25
* @route '/verify-otp'
*/
verify.url = (options?: RouteQueryOptions) => {




    return verify.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OTPController::verify
* @see app/Http/Controllers/Auth/OTPController.php:25
* @route '/verify-otp'
*/
verify.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verify.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::verify
* @see app/Http/Controllers/Auth/OTPController.php:25
* @route '/verify-otp'
*/
verify.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: verify.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::verify
* @see app/Http/Controllers/Auth/OTPController.php:25
* @route '/verify-otp'
*/
const verifyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verify.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::verify
* @see app/Http/Controllers/Auth/OTPController.php:25
* @route '/verify-otp'
*/
verifyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verify.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::verify
* @see app/Http/Controllers/Auth/OTPController.php:25
* @route '/verify-otp'
*/
verifyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verify.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

verify.form = verifyForm

/**
* @see \App\Http\Controllers\Auth\OTPController::resend
* @see app/Http/Controllers/Auth/OTPController.php:91
* @route '/resend-otp'
*/
export const resend = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(options),
    method: 'post',
})

resend.definition = {
    methods: ["post"],
    url: '/resend-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\OTPController::resend
* @see app/Http/Controllers/Auth/OTPController.php:91
* @route '/resend-otp'
*/
resend.url = (options?: RouteQueryOptions) => {




    return resend.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OTPController::resend
* @see app/Http/Controllers/Auth/OTPController.php:91
* @route '/resend-otp'
*/
resend.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::resend
* @see app/Http/Controllers/Auth/OTPController.php:91
* @route '/resend-otp'
*/
const resendForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resend.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\OTPController::resend
* @see app/Http/Controllers/Auth/OTPController.php:91
* @route '/resend-otp'
*/
resendForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resend.url(options),
    method: 'post',
})

resend.form = resendForm



const otp = {
    verify: Object.assign(verify, verify8ef1b2),
    resend: Object.assign(resend, resend),
}

export default otp