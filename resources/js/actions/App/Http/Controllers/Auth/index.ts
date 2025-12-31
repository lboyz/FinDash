import LoginController from './LoginController'
import ForgotPasswordController from './ForgotPasswordController'
import ResetPasswordController from './ResetPasswordController'
import RegisterController from './RegisterController'
import OTPController from './OTPController'


const Auth = {
    LoginController: Object.assign(LoginController, LoginController),
    ForgotPasswordController: Object.assign(ForgotPasswordController, ForgotPasswordController),
    ResetPasswordController: Object.assign(ResetPasswordController, ResetPasswordController),
    RegisterController: Object.assign(RegisterController, RegisterController),
    OTPController: Object.assign(OTPController, OTPController),
}

export default Auth