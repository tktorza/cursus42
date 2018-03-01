%define WRITE 4
%define STDOUT 1
%define SYSCALL(nb) 0x2000000 | nb

section .text
global _ft_puts
extern _ft_strlen
_ft_puts:
    mov rax, 0
    cmp rdi, 0
    je return
    mov rsi, rdi
    call _ft_strlen
    mov rdi, STDOUT
    mov rdx, rax
    mov rax, SYSCALL(WRITE)    
    syscall
    ;leave

return:
    ret
