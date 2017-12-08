section .text
global _decrypt_true
extern _ft_putnbr
extern _ft_putchar

_decrypt_true:
;rdi = *data
   
    push rbp
    mov rbp, rsp
    mov r15, 1
    mov r14, rcx
    push rcx
    push rdx
    dec rcx
;;debugg
    push r8                ;debug
    push rcx               ;debug
    push rdx               ;debug
    push rsi               ;debug
    push rdi
    
    pop rdi               ;debug
    mov rdi, [rdi + rcx]

    call _ft_putnbr        ;debugg
    mov rdi, 10
    call _ft_putchar
    pop rdi               ;debug
    call _ft_putnbr
    mov rdi, 10
    call _ft_putchar        ;debugg
    pop rdi               ;debug
    call _ft_putnbr
    mov rdi, 10
    call _ft_putchar        ;debugg
    pop rdi               ;debug
    call _ft_putnbr
    mov rdi, 10
    call _ft_putchar        ;debugg
    pop rdi               ;debug
    call _ft_putnbr
    mov rdi, 10
    call _ft_putchar        ;debugg
    pop rdi               ;debug
    call _ft_putnbr
    mov rdi, 10
    call _ft_putchar        ;debugg
    pop rdi               ;debug
    call _ft_putnbr
    mov rdi, 10
    call _ft_putchar        ;debugg
;;degugg
loop:
    push rdi                ;debuggg
    mov rdi, rcx                ;debuggg
    call _ft_putnbr             ;debuggg
    pop rdi             ;debuggg


    inc rcx
    mov rdi, [rdi + rcx]
    cmp rcx, rdx
    je return
    mov rbx, rdi
    cmp r15, 0
    jl withless
    pop rcx
    pop rdx
    mov rax, rsi
    imul rax, 2
    and rdx, rax
    sub rbx, rdx
    shl rcx, 1
    shr rdx, 1
    add rbx, rcx
    add rbx, rdx
    mov rdi, rbx

index:
    sub rcx, 14
    push rdx
    push rcx
    mov rax, rcx
    mov rcx, r8
    mov rdx, 0
    div rcx
    cmp rdx, 0
    je key_index
    cmp r15, 0
    jg pos
    cmp rsi, 2
    jg less
    mov r15, 1
    jmp loop

key_index:
    mov rax, r8
    mov rcx, 7
    div rcx
    mov rsi, rax
    jmp loop

pos:
    cmp rsi, 64
    jl plus
    mov r15, -1

plus:
    imul rsi, 2

less:
    mov rax, rsi
    mov rcx, 2
    div rcx
    mov rsi, rax
    jmp loop

withless:
    mov rax, rsi
    mov rcx, 2
    div rcx
    pop rcx
    pop r13
    and r13, rax
    sub rbx, r13
    shr rcx, 1

    shl r13, 1
    add rbx, rcx
    add rbx, r13
    mov rdi, rbx

return:
    pop rcx
    pop rdx
    mov rsp, rbp
    pop rbp
    ret