import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, AbstractControl, ValidationErrors } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ToastrService } from 'ngx-toastr';

import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatSelectModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})
export class RegisterComponent {
  registerForm: FormGroup;
  isLoading = signal(false);
  hidePassword = signal(true);
  hidePasswordConfirm = signal(true);

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private toastr: ToastrService
  ) {
    this.registerForm = this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      prenom: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      telephone: ['', [Validators.required, Validators.pattern(/^(\+221)?(77|78|76|70|75)[0-9]{7}$/)]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      password_confirmation: ['', [Validators.required]],
      adresse: [''],
      ville: ['']
    }, {
      validators: this.passwordMatchValidator
    });
  }

  /**
   * Validator personnalisé : mot de passe et confirmation doivent correspondre
   */
  passwordMatchValidator(control: AbstractControl): ValidationErrors | null {
    const password = control.get('password');
    const passwordConfirm = control.get('password_confirmation');

    if (!password || !passwordConfirm) {
      return null;
    }

    return password.value === passwordConfirm.value ? null : { passwordMismatch: true };
  }

  /**
   * Soumission du formulaire
   */
  onSubmit(): void {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      this.toastr.warning('Veuillez remplir tous les champs correctement', 'Formulaire invalide');
      return;
    }

    this.isLoading.set(true);

    const formData = this.registerForm.value;

    this.authService.register(formData).subscribe({
      next: (response) => {
        this.isLoading.set(false);
        this.toastr.success(
          'Votre compte a été créé avec succès ! Un agent va vérifier votre inscription.',
          'Inscription réussie',
          { timeOut: 6000 }
        );
        
        // Redirection vers login (car compte en attente d'activation)
        setTimeout(() => {
          this.router.navigate(['/auth/login']);
        }, 2000);
      },
      error: (error) => {
        this.isLoading.set(false);
        
        // Afficher erreurs de validation détaillées
        if (error.errors) {
          Object.keys(error.errors).forEach(key => {
            error.errors[key].forEach((msg: string) => {
              this.toastr.error(msg, 'Erreur de validation');
            });
          });
        } else {
          this.toastr.error(error.message || 'Erreur lors de l\'inscription', 'Erreur');
        }
      }
    });
  }

  /**
   * Toggle visibilité mot de passe
   */
  togglePasswordVisibility(field: 'password' | 'confirm'): void {
    if (field === 'password') {
      this.hidePassword.update(value => !value);
    } else {
      this.hidePasswordConfirm.update(value => !value);
    }
  }

  /**
   * Récupérer message d'erreur pour un champ
   */
  getErrorMessage(fieldName: string): string {
    const field = this.registerForm.get(fieldName);
    
    if (field?.hasError('required')) {
      return 'Ce champ est requis';
    }
    if (field?.hasError('email')) {
      return 'Email invalide';
    }
    if (field?.hasError('minlength')) {
      const minLength = field.errors?.['minlength'].requiredLength;
      return `Minimum ${minLength} caractères`;
    }
    if (field?.hasError('pattern') && fieldName === 'telephone') {
      return 'Format: +221771234567 ou 771234567';
    }
    
    return '';
  }

  /**
   * Vérifier si les mots de passe correspondent
   */
  get passwordMismatch(): boolean {
    return this.registerForm.hasError('passwordMismatch') && 
           this.registerForm.get('password_confirmation')?.touched || false;
  }
}

