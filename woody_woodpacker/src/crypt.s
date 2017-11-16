section .data
keyfact db 15342

section .text
global _ft_crypt
_ft_crypt:
    push rbp
    mov rbp, rsp
    sub rbp, 16
    xor rax, rax
    push rdi

key:
    cmp rdi, 0
    return
    mov rax, rdi
    div keyfact
    push rax
    mov rdx, rax
    ;to hex
    ;loop :  for each ^10 of hexa --> + ascii de val (10(=A) donc + 65 (A en ascii) )

alpha:
    add rdx, 64
    mov rbx, rdx
    jmp changekey

keyloop:
    
    div rax
    ;compare premiere 'lettre' --> rbx
    cmp rdx, 10
    jl alpha ;si c'est une lettre en hexa
    add rdx, 48 ;sinon
    mov rbx, rdx

changekey:
    pop rdx ;on récupère la valeur de depart
    add rdx, rbx ; on ajoute la valeur ascii du reste
    push rdx ; on la repush
    cmp rax, 0
    jne keyloop

crypt:
    pop rbx ;on récup la val de la key
    pop rdi ;on récupère le début de la section
    push rdi ;

cryptloop:
    cmp rdi, rsi
    je return
    mov rax, rdi
    div 42
    add rax, rbx
    mul 2
    mov rdi, rax
    inc rdi
    jmp cryptloop

return:
    pop rax
    pop rbp
    ret