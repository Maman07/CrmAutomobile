pipeline {
    agent any
    
    environment {
        DOCKER_COMPOSE_FILE = 'docker-compose.yml'
        SONAR_HOST_URL = 'http://sonarqube:9000'
        SONAR_PROJECT_KEY = 'autotech-crm'
    }
    
    stages {
        stage('📋 Checkout') {
            steps {
                echo '🔄 Récupération du code source...'
                checkout scm
            }
        }
        
        stage('🔍 Analyse SonarQube - Backend') {
            steps {
                echo '📊 Analyse de qualité du code Backend (Laravel)...'
                dir('backend') {
                    script {
                        def scannerHome = tool 'SonarQubeScanner'
                        withSonarQubeEnv('SonarQube') {
                            sh """
                                ${scannerHome}/bin/sonar-scanner \
                                -Dsonar.projectKey=${SONAR_PROJECT_KEY}-backend \
                                -Dsonar.sources=app \
                                -Dsonar.host.url=${SONAR_HOST_URL} \
                                -Dsonar.php.coverage.reportPaths=coverage.xml
                            """
                        }
                    }
                }
            }
        }
        
        stage('🔍 Analyse SonarQube - Frontend') {
            steps {
                echo '📊 Analyse de qualité du code Frontend (Angular)...'
                dir('frontend') {
                    script {
                        def scannerHome = tool 'SonarQubeScanner'
                        withSonarQubeEnv('SonarQube') {
                            sh """
                                ${scannerHome}/bin/sonar-scanner \
                                -Dsonar.projectKey=${SONAR_PROJECT_KEY}-frontend \
                                -Dsonar.sources=src \
                                -Dsonar.host.url=${SONAR_HOST_URL} \
                                -Dsonar.typescript.lcov.reportPaths=coverage/lcov.info
                            """
                        }
                    }
                }
            }
        }
        
        stage('✅ Quality Gate') {
            steps {
                echo '⏳ Attente du Quality Gate SonarQube...'
                timeout(time: 5, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }
        
        stage('🧪 Tests Backend') {
            steps {
                echo '🧪 Exécution des tests Backend...'
                dir('backend') {
                    sh '''
                        composer install
                        php artisan test --coverage
                    '''
                }
            }
        }
        
        stage('🧪 Tests Frontend') {
            steps {
                echo '🧪 Exécution des tests Frontend...'
                dir('frontend') {
                    sh '''
                        npm ci
                        npm run test -- --watch=false --code-coverage
                    '''
                }
            }
        }
        
        stage('🏗️ Build Docker Images') {
            steps {
                echo '🐳 Construction des images Docker...'
                sh '''
                    docker-compose build --no-cache
                '''
            }
        }
        
        stage('🚀 Deploy') {
            steps {
                echo '🚀 Déploiement des services...'
                sh '''
                    docker-compose down
                    docker-compose up -d
                '''
            }
        }
        
        stage('✅ Health Check') {
            steps {
                echo '🏥 Vérification de la santé des services...'
                sh '''
                    sleep 10
                    curl -f http://localhost:8000/api/health || exit 1
                    curl -f http://localhost:4200 || exit 1
                '''
            }
        }
    }
    
    post {
        success {
            echo '✅ Pipeline exécuté avec succès!'
            emailext (
                subject: "✅ Build Success: ${env.JOB_NAME} - ${env.BUILD_NUMBER}",
                body: """
                    Le pipeline a été exécuté avec succès!
                    
                    Projet: ${env.JOB_NAME}
                    Build: ${env.BUILD_NUMBER}
                    URL: ${env.BUILD_URL}
                """,
                to: 'dev@autotech.sn'
            )
        }
        failure {
            echo '❌ Le pipeline a échoué!'
            emailext (
                subject: "❌ Build Failed: ${env.JOB_NAME} - ${env.BUILD_NUMBER}",
                body: """
                    Le pipeline a échoué!
                    
                    Projet: ${env.JOB_NAME}
                    Build: ${env.BUILD_NUMBER}
                    URL: ${env.BUILD_URL}
                    
                    Veuillez vérifier les logs.
                """,
                to: 'dev@autotech.sn'
            )
        }
        always {
            echo '🧹 Nettoyage...'
            cleanWs()
        }
    }
}

