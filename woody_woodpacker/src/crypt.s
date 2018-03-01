section .data
keyfact DQ 1534

section .text
global _ft_cryptom
_ft_cryptom:
    push rbp
    mov rbp, rsp
    sub rbp, 16
    xor rax, rax
    mov r15, 55
    push rdi

key:
    cmp rdi, 0
    je return
    mov rax, rdi
    ;div 5
    div r15
    pop rdi
;    push rax
;    mov rdx, rax
;    ;to hex
;    ;loop :  for each ^10 of hexa --> + ascii de val (10(=A) donc + 65 (A en ascii) )
;    mov r15, 10
;
;keyloop:
;    div r15
;    ;compare premiere 'lettre' --> rbx
;    cmp rdx, 10
;    jl alpha ;si c'est une lettre en hexa
;    add rdx, 48 ;sinon
;    mov rbx, rdx
;
;changekey:
;    pop rdx ;on récupère la valeur de depart
;    add rdx, rbx ; on ajoute la valeur ascii du reste
;    push rdx ; on la repush
;    cmp rax, 0
;    jne keyloop
;
;alpha:
;    add rdx, 64
;    mov rbx, rdx
;    jmp changekey

;crypt:
;    mov r15, 42
;    mov r14, 2
;    pop rbx ;on récup la val de la key
;    pop rdi ;on récupère le début de la section
;    push rdi ;
;
;cryptloop:
;    cmp rdi, rsi
;    je return
;    mov rax, rdi
;    div r15
;    add rax, rbx
;    mul r14
;    mov rdi, rax
;    inc rdi
;    jmp cryptloop
;
return:
    ;pop rax
    pop rbp
    ;leave
    ret